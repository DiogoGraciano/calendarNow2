    function avisoCookies({
        message='Utilizamos cookies para que vocÃª tenha a melhor experiÃªncia em nosso site. Para saber mais acesse nossa pÃ¡gina de PolÃ­tica de Privacidade',
    }){
        var check = localStorage.getItem('avisoCookies')
        if(!check){
            var body = document.getElementsByTagName('body')[0];
            body.innerHTML += `
            <div id="aviso-cookies">
                <span id="texto-cookies">${message}</span>
                <button class="btn btn-primary" id="entendi-cookies">Entendi</button>
            </div>`;
            document.getElementById('entendi-cookies').addEventListener('click', function(){
                localStorage.setItem("avisoCookies", "accept");
                document.getElementById('aviso-cookies').remove()
            })
        }
    }

    function showLoader() {
        if (!document.getElementById("loader")) {
            var loaderDiv = document.createElement("div");
            loaderDiv.id = "loader";
            document.documentElement.appendChild(loaderDiv);
        } else {
            document.getElementById("loader").style.display = "block";
        }
    }
    
    function removeLoader() {
        document.getElementById("loader").style.display = "none";
    }

    function validaVazio(seletor) {
        var valor = seletor.value;
    
        if (valor === '') { 
            seletor.classList.remove('is-valid');
            seletor.classList.add('is-invalid');
            return false; 
        }
    
        seletor.classList.remove('is-invalid');
    }

    function validaTelefone(telefone) {
        var telefone = telefone.value.replace(/\D/g, '');

        if (!(telefone.length >= 10 && telefone.length <= 11)) {
            document.getElementById("telefone").classList.remove('is-valid');
            document.getElementById("telefone").classList.add('is-invalid');
            return false;
        }

        if (telefone.length === 11 && parseInt(telefone.substring(2, 3)) !== 9) {
            document.getElementById("telefone").classList.remove('is-valid');
            document.getElementById("telefone").classList.add('is-invalid');
            return false;
        }

        for (var n = 0; n < 10; n++) {
            if (telefone === new Array(11).join(n) || telefone === new Array(12).join(n)) {
                document.getElementById("telefone").classList.remove('is-valid');
                document.getElementById("telefone").classList.add('is-invalid');
                return false;
            }
        }
        
        var codigosDDD = [11, 12, 13, 14, 15, 16, 17, 18, 19,
            21, 22, 24, 27, 28, 31, 32, 33, 34,
            35, 37, 38, 41, 42, 43, 44, 45, 46,
            47, 48, 49, 51, 53, 54, 55, 61, 62,
            64, 63, 65, 66, 67, 68, 69, 71, 73,
            74, 75, 77, 79, 81, 82, 83, 84, 85,
            86, 87, 88, 89, 91, 92, 93, 94, 95,
            96, 97, 98, 99];
        
        if (codigosDDD.indexOf(parseInt(telefone.substring(0, 2))) === -1) { 
            document.getElementById("telefone").classList.remove('is-valid');
            document.getElementById("telefone").classList.add('is-invalid');
            return false;
        }

        document.getElementById("telefone").classList.remove('is-invalid');
        return true;
    }

    function validaEmail() {
        var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
        var email = document.getElementById("email").value;
        
        if (email === '' || !er.test(email)) { 
            document.getElementById("email").classList.remove('is-valid');
            document.getElementById("email").classList.add('is-invalid');
            return false; 
        }

        document.getElementById("email").classList.remove('is-invalid');
    }

    function setEvents(){
        let emails = document.querySelectorAll("input[type=email]")
        if(emails.length){
            emails.forEach(function(input) {
                input.addEventListener("blur", function() {
                    validaEmail(this);
                });
            });
        }

        let requireds = document.querySelectorAll("input[required]")
        if(requireds.length){
            requireds.forEach(function(input) {
                input.addEventListener("blur", function() {
                    validaVazio(this);
                });
            });
        }

        let textareaRequired = document.querySelectorAll("textarea[required]")
        if(textareaRequired.length){
            textareaRequired.forEach(function(input) {
                input.addEventListener("blur", function() {
                    validaVazio(this);
                });
            });
        }

        let selectRequired = document.querySelectorAll("select[required]")
        if(selectRequired.length){
            selectRequired.forEach(function(input) {
                input.addEventListener("blur", function() {
                    validaVazio(this);
                });
            });
        }

        let tels = document.querySelectorAll("input[type=tel]");
        if(tels.length){
            tels.forEach(function(input) {
                input.addEventListener("blur",function() {
                    validaTelefone(this);
                });
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function() {

        htmx.config.globalViewTransitions = true;
        htmx.config.defaultFocusScroll = true;

        setEvents();

        document.body.addEventListener('htmx:xhr:loadstart', function(evt) {
            showLoader();
        });

        document.body.addEventListener('htmx:afterSettle', function(evt) {
            setEvents();
            removeLoader();
        });

        if(localStorage.getItem("avisoCookies") != "accept"){
            avisoCookies({
                message:'Utilizamos cookies para que você tenha a melhor experiência em nosso site. Para saber mais acesse nossa página de <a href="\\privacidade">Política de Privacidade</a>'
            });
        }
    });