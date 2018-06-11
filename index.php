<?php include("/home/chrnet/www/sites/mailtrust/includes/header.php"); ?>
<div  id="story">
  <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
    <h1 class="display-4">Vérifier ma boite email</h1>
    <p class="lead">Vous recevez des centaines d'e-mails que vous ne lisez pas ? Nous vous aidons à les supprimer en quelques secondes</p>
    <div class="display-4 col-md-6 offset-md-3 ">
      <button  id="authorize-button" type="button" class="btn btn-lg btn-block btn-google">
      <i class="fab fa-google"></i> Nettoyer ma boite Gmail</button>
    </div>
  </div>
  <div class="container">
    <div class="px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
      <h1 class="display-4">Comment ca marche ?</h1>
      <p class="lead">Nous analysons vos emails et l'expéditeur associé pour générer un score en fonction leur envoi d'emails et de l'utilisation de vos données.</p>
      <p class="lead">Un expediteur à une mauvaise note ou ne vous interesse pas ? Vous pouvez vous désinscrire en un clic.</p>
    </div>
  </div>
</div>
    <div id="content" style="display : none">
      <div class="row">
      <div  class="col-4">
        <div class="list-group">
          <div class="list-group-item">
            <h4 class="list-group-item-heading" id="to">...</h4>
            <p class="list-group-item-text" id="count">...</p>
              <button  id="signout-button" type="button" class="btn btn-block btn-google">
              <i class="fas fa-sign-out-alt"></i>Déconnexion</button>
          </div>
        </div>
        <ul class="list-group" id="labels">

        </ul>
      </div>
      <div class="col-8">
        <h2>Liste des expediteurs
        </h2>
        <h3>
          <button id="all" class="btn btn-s btn-secondary" type="button" id="dropdownMenuButton" >
              Tout selectionner
          </button>
            <div id="drowdown_all" class="dropdown">
            <button id="all" class="btn btn-s btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Me désabonner & supprimer les emails
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="#" onclick='alertSW()'>Me désabonner & supprimer les emails</a>
            <a class="dropdown-item" href="#" onclick='alertSW()'>Me désabonner seulement</a>
            <a class="dropdown-item" href="#" onclick='alertSW()'>Ne rien faire</a>
            </div>
            </div>
          </h3>
        <ul class="list-group" id="emails">
          <div id="loading">
            <div class="fa-3x">
              <i class="fas fa-cog fa-spin"></i>
            </div>
            <p>
              Initialisation de votre boite email
            </p>
          </div>
        </ul>
      </div>
    </div>
    </div>



      <script type="text/javascript">
        // Client ID and API key from the Developer Console
        var CLIENT_ID = '391195035726-vcdej6ncas5e9q5a8n1kflptbcrfsh88.apps.googleusercontent.com';

        // Array of API discovery doc URLs for APIs used by the quickstart
        var DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest"];

        // Authorization scopes required by the API; multiple scopes can be
        // included, separated by spaces.
        var SCOPES = 'https://www.googleapis.com/auth/gmail.readonly';

        var authorizeButton = document.getElementById('authorize-button');
        var signoutButton = document.getElementById('signout-button');
        var story = document.getElementById('story');
        var content = document.getElementById('content');
        var to = document.getElementById('to');
        var count = document.getElementById('count');
        var emails = document.getElementById('emails');
        var all = document.getElementById('all');
        var list = [];
        var list_ordered = [];

        function alertSW(){
          alert('Nous travaillons dur, celà sera bientôt disponible !');
        }
        /**
         *  On load, called to load the auth2 library and API client library.
         */
        function handleClientLoad() {
          gapi.load('client:auth2', initClient);
        }

        /**
         *  Initializes the API client library and sets up sign-in state
         *  listeners.
         */
        function initClient() {
          gapi.client.init({
            discoveryDocs: DISCOVERY_DOCS,
            clientId: CLIENT_ID,
            scope: SCOPES
          }).then(function () {
            // Listen for sign-in state changes.
            gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

            // Handle the initial sign-in state.
            updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
            authorizeButton.onclick = handleAuthClick;
            signoutButton.onclick = handleSignoutClick;
          });
        }

        /**
         *  Called when the signed in status changes, to update the UI
         *  appropriately. After a sign-in, the API is called.
         */
        function updateSigninStatus(isSignedIn) {
          if (isSignedIn) {
            authorizeButton.style.display = 'none';
            signoutButton.style.display = 'block';
            story.style.display = 'none';
            content.style.display = 'block';
            listLabels();
            listMessages();
          } else {
            authorizeButton.style.display = 'block';
            story.style.display = 'block';
            content.style.display = 'none';
            signoutButton.style.display = 'none';
            all.style.display = 'none';
          }
        }

        /**
         *  Sign in the user upon button click.
         */
        function handleAuthClick(event) {
          gapi.auth2.getAuthInstance().signIn();
        }

        /**
         *  Sign out the user upon button click.
         */
        function handleSignoutClick(event) {
          gapi.auth2.getAuthInstance().signOut();
        }

        /**
         * Append a pre element to the body containing the given message
         * as its text node. Used to display the results of the API call.
         *
         * @param {string} message Text to be placed in pre element.
         */
        function appendPre(id, message) {
          var pre = document.getElementById(id);
          // if(id== 'labels')
          // var textContent = document.createElement('' + message + '');
          if(pre == null)
            console.error(id + "is null");

          pre.innerHTML = pre.innerHTML + message;
        }

        /**
         * Print all Labels in the authorized user's inbox. If no labels
         * are found an appropriate message is printed.
         */
        function listLabels() {
          gapi.client.gmail.users.labels.list({
            'userId': 'me'
          }).then(function(response) {
            var labels = response.result.labels;

            if (labels && labels.length > 0) {
              for (i = 0; i < labels.length; i++) {
                var label = labels[i];
                var count = Math.floor(Math.random() * Math.floor(150));
                html = "";
                if(label.name == "CHAT")
                  html = '<span class="label label-default">Chat</span>';
                else if(label.name == "CATEGORY_FORUMS")
                  html = '<span class="label label-default">Forums</span>';
                else if(label.name == "CATEGORY_SOCIAL")
                  html = '<span class="label label-primary">Réseaux sociaux</span>';
                else if(label.name == "CATEGORY_PERSONAL")
                  html = '<span class="label label-info">Personnel</span>';
                else if(label.name == "CATEGORY_UPDATES")
                  html = '<span class="label label-success">Mise à jour</span>';
                else if(label.name == "CATEGORY_PROMOTIONS")
                  html = '<span class="label label-warning">Promotions</span>';
                else if(label.name == "CATEGORY_SPAM")
                  html = '<span class="label label-danger">SPAM</span>';
                else
                  html = label.name;


                appendPre('labels','<li class="list-group-item"><span class="badge"><i class="fas fa-envelope"></i>' + count + '</span>' +
                  '' + html + '' +
                '</li>');
              }
            } else {
              appendPre('labels','No Labels found.');
            }
          });
        }

        function listMessages() {
          // console.log("listMessages");
          var getPageOfMessages = function(request, result) {
            request.execute(function(resp) {
              result = result.concat(resp.messages);
              var nextPageToken = resp.nextPageToken;
              if (nextPageToken && result.length < 1000) {
                request = gapi.client.gmail.users.messages.list({
                  'userId': 'me',
                  'pageToken': nextPageToken,
                  'q': []
                });
                  // console.log("result",result);
                  // showMails(result);
                setLoading(result.length);
                getPageOfMessages(request, result);
              } else {
                // console.log("result",result);
                showMails(result);
              }
            });
          };
          var initialRequest = gapi.client.gmail.users.messages.list({
            'userId': 'me',
            'q': []
          });
          getPageOfMessages(initialRequest, []);
          // showMails(initialRequest);
        }


        async function showMails(data){
          // console.log("showMails",data);
          list = [];
          index = 0;
          total = data.length;

          function getIndex(from){
            // console.log("getIndex : ", from);
            for(let key in list){
              // console.log("from : " + from + " - key => " + key + " : ",list[key]);
              if(from == list[key]["from"]){
                // console.log("return ", key);
                return key;
              }
              // if(list[key])
            }
            return -1;
          }
          function addMail(resp,current_index){
            // From == 22
            //Check headers
            // console.log("addMail",current_index);

            var from = "";
            var to = "";
            var subject = "";
            var labels = resp.labelIds;
            for (var j = 0; j < resp.payload.headers.length; j++){
              // look for the entry with a matching `code` value
              // console.log("resp.payload.headers[" + j + "].name",resp.payload.headers[j].name);
              // console.log("resp.payload.headers[" + j + "].value",resp.payload.headers[j].value);
              if (resp.payload.headers[j].name == "From")
                from = resp.payload.headers[j].value;
              if (resp.payload.headers[j].name == "To")
                to = resp.payload.headers[j].value;
              if (resp.payload.headers[j].name == "Subject")
                subject = resp.payload.headers[j].value;
            }
            // console.log("from",from);



            email = {
              from : from,
              to : to,
              subject:subject
            }

            let index = getIndex(from);
            // console.log("index",index);
            if(index == -1){
              list.push({
                from: from,
                labels: labels,
                emails:[]
              });

              list[list.length-1]["emails"].push(email);

                // console.log("create list[" + (list.length-1) + "] for " + from + " => ",list);
            } else {
              // console.log("add " + from + " to list[" + index + "] ",list);
              list[index]["emails"].push(email);
            }
            // console.log("data",data);
            // console.log("index == total : " + current_index + " == " + total - 1);
            if(current_index == total - 1){
              // console.log("result total (" + i + " == " + total + ")",list);
              addToList(list);
              setUserInfo(email, total);
            }
          }

          let count = 0;
          for(var i = 0; i < total; i++){

            var initialRequest = gapi.client.gmail.users.messages.get({
              'userId': 'me',
              'id': data[i].id
            });
            var getMessage = await function(request, result) {
              request.execute(function(resp) {
                // console.log("resp",resp);
                addMail(resp,count++);
              });
            }
            var message = getMessage(initialRequest,[]);
          }
        }

        function addToList(items){
          // console.log("list",list);
          // console.log("items",items);
          // // console.log("ibjet keys items",Object.keys(items));
          //
          // for(let key of Object.keys(items)){
          //   // console.log("items[" + key + "]",items[key]);
          //   if(list[key] != null){
          //     list[key]["emails"] = list[key]["emails"].concat(items.emails);
          //   }
          //   // list = list.concat(items);
          //   //
          // }
          list.sort(function (a, b) {
              var a_length = a.emails.length;
              var b_length = b.emails.length;
              return b_length - a_length;
          });
          // console.log("list", list);
          list_to_html(list);
        }

        function list_to_html(list){
          // console.log("list",list);
          emails.innerHTML = "";
          for(sender of list){
            // console.log("sender",sender);
            var labels = "";
            if(sender.labels == null)
              labels = '';
            else if(sender.labels.includes("CHAT"))
              labels = '<span class="label label-default">Chat</span>';
            else if(sender.labels.includes("CATEGORY_FORUM"))
              labels = '<span class="label label-default">Forums</span>';
            else if(sender.labels.includes("CATEGORY_SOCIAL"))
              labels = '<span class="label label-primary">Réseaux sociaux</span>';
            else if(sender.labels.includes("CATEGORY_PERSONAL"))
              labels = '<span class="label label-info">Personnel</span>';
            else if(sender.labels.includes("CATEGORY_UPDATES"))
              labels = '<span class="label label-success">Mise à jour</span>';
            else if(sender.labels.includes("CATEGORY_PROMOTIONS"))
              labels = '<span class="label label-warning">Promotions</span>';
            else if(sender.labels.includes("CATEGORY_SPAM"))
              labels = '<span class="label label-danger">SPAM</span>';

            var frequency = ["2/semaines","1/jour","4/semaines","2/mois","1/mois"];
            var notation = ["a","b","c","d","e"];
            frequency = frequency[Math.floor(Math.random() * frequency.length)];
            notation =notation[Math.floor(Math.random() * notation.length)];

            var dropdown = '';
            dropdown+='<button type="button" class="btn btn-default btn-lg"><i class="fas fa-trash-alt"></i></button>';
            dropdown+='<button type="button" class="btn btn-default btn-lg"><i class="fas fa-star"></i></button>';
            // '<div id="drowdown" class="dropdown">'+
            //   '<button class="btn btn-xs btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'+
            // '    Me désabonner & supprimer les emails'+
            // '  </button>'+
            //   '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'+
            //     '<a class="dropdown-item" onclick="alertSW()" href="#">Me désabonner & supprimer les emails</a>'+
            //     '<a class="dropdown-item" onclick="alertSW()" href="#">Me désabonner seulement</a>'+
            //     '<a class="dropdown-item" onclick="alertSW()" href="#">Ne rien faire</a>'+
            //   '</div>'+
            // '</div>';
            emails.innerHTML += '<li  class="list-group-item list_item"><input type="checkbox" /><div class="box"><div class="counter"><i class="fas fa-envelope"></i>' + sender.emails.length + '</span></div><span class="frequence">' + frequency + '</span></div><div class="notation"><span class="badge badge-' + notation +'">' + notation + '</span></div><h4>' +  sender.from + '<span class="badge"></h4><div class="labels">' + labels + '</div>' + dropdown + '</li>';
          }

        }
        function setUserInfo(data,total){
          console.log("setUserInfo",data);

          to.innerHTML = data.to.replace("<","").replace(">","");
          count.innerHTML = total + " emails";

          all.style.display = 'block';

        }

        function setLoading(x){
          if(x == 900)
            emails.innerHTML = '<div id="loading"><div class="fa-3x"><i class="fas fa-cog fa-spin"></i></div><p>Préparation de la liste ....</p></div>';
          else
            emails.innerHTML = '<div id="loading"><div class="fa-3x"><i class="fas fa-cog fa-spin"></i></div><p>Chargement des expediteurs (' + (x + 100) + '/1000)</p></div>';
        }

      </script>

      <script async defer src="https://apis.google.com/js/api.js"
        onload="this.onload=function(){};handleClientLoad()"
        onreadystatechange="if (this.readyState === 'complete') this.onload()">
      </script>
      <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-120527644-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-120527644-1');
</script>

      <?php include("/home/chrnet/www/sites/mailtrust/includes/footer.php"); ?>
