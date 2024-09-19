function go(url) {
    window.location.href = url;
}

function avisoCookies({
    message = 'Utilizamos cookies para que vocÃª tenha a melhor experiÃªncia em nosso site. Para saber mais acesse nossa pÃ¡gina de PolÃ­tica de Privacidade',
}) {
    var check = localStorage.getItem('avisoCookies')
    if (!check) {
        var body = document.getElementsByTagName('body')[0];
        body.innerHTML += `
            <div id="aviso-cookies">
                <span id="texto-cookies">${message}</span>
                <button class="btn btn-primary" id="entendi-cookies">Entendi</button>
            </div>`;
        document.getElementById('entendi-cookies').addEventListener('click', function () {
            localStorage.setItem("avisoCookies", "accept");
            document.getElementById('aviso-cookies').remove()
        })
    }
}

function calcularMinutos(tempo) {
    var partes = tempo.split(":");
    return parseInt(partes[0]) * 60 + parseInt(partes[1]);
}

function multiplicarTempo(tempo, qtd) {

    var partes = tempo.split(":");
    var horas = parseInt(partes[0]) * qtd;
    var minutos = parseInt(partes[1]) * qtd;

    while (minutos >= 60) {
        minutos -= 60;
        horas++;
    }

    return horas.toString().padStart(2, '0') + ":" + minutos.toString().padStart(2, '0');
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

function mensagem(mensagem, type = "alert-danger") {
    var alertDiv = document.createElement("div");
    alertDiv.className = "alert " + type + " alert-dismissible mt-1 d-flex justify-content-between align-items-center";
    alertDiv.role = "alert";
    alertDiv.innerHTML = mensagem + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    document.body.prepend(alertDiv);
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

function getInvalid(mensagem, id) {
    return '<div id="' + id + '" class="invalid-feedback">' + mensagem + '</div>';
}

function getValid(mensagem, id) {
    return '<div id="' + id + '" class="valid-feedback">' + mensagem + '</div>';
}

function loadChoices() {
    let multipleText = document.querySelectorAll("input[type=multiple-text]");
    let select = document.querySelectorAll("select");

    if (!document.querySelectorAll(".choices__inner").length) {
        if (select.length) {
            select.forEach(element => {
                new Choices(element, {
                    noResultsText: 'resultados não encontrados',
                    itemSelectText: 'Precione para selecionar',
                });
            });
        }

        if (multipleText.length) {
            multipleText.forEach(element => {
                new Choices(element, {
                    delimiter: ',',
                    editItems: true,
                    removeItemButton: true,
                    addItemText: (value) => {
                        return `Aperte Enter para adicionar <b>"${value}"</b>`;
                    },
                });
            });
        }
    }
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

function setEvents() {
    let emails = document.querySelectorAll("input[type=email]")
    if (emails.length) {
        emails.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaEmail(this);
            });
        });
    }

    let requireds = document.querySelectorAll("input[required]")
    if (requireds.length) {
        requireds.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaVazio(this);
            });
        });
    }

    let srequireds = document.querySelectorAll("select[required]")
    if (srequireds.length) {
        srequireds.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaVazio(this);
            });
        });
    }

    let textarea = document.querySelectorAll("textarea[required]")
    if (textarea.length) {
        textarea.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaVazio(this);
            });
        });
    }

    let tels = document.querySelectorAll("input[type=tel]");
    if (tels.length) {
        tels.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaTelefone(this);
            });
        });
    }

    let btnMarcar = document.querySelector("button#btn_massaction_marcar");
    if (btnMarcar) {
        btnMarcar.addEventListener("click", function () {
            let checkboxs = document.querySelectorAll("#massaction");
            if (checkboxs.length) {
                checkboxs.forEach(function (input) {
                    input.checked = true;
                });
            }
        })
    }

    let btnDesmarcar = document.querySelector("button#btn_massaction_desmarcar");
    if (btnDesmarcar) {
        btnDesmarcar.addEventListener("click", function () {
            let checkboxs = document.querySelectorAll("#massaction");
            if (checkboxs.length) {
                checkboxs.forEach(function (input) {
                    input.checked = false;
                });
            }
        })
    }

    document.querySelectorAll(".qtd_item").forEach(function (element) {
        element.addEventListener("change", function () {
            var index = element.getAttribute('data-index-servico');
            var qtd = parseInt(element.value);

            var checkboxServico = document.querySelector("#servico_index_" + index);
            if (checkboxServico.checked) {
                document.querySelector('input[data-index-check="' + index + '"]').checked = false;

                var totalElement = document.querySelector("#total");
                var totalAtual = parseFloat(totalElement.getAttribute('data-vl-total')) || 0;
                var totalItem = parseFloat(document.querySelector("#total_item_" + index).getAttribute('data-vl-atual')) || 0;

                if (checkboxServico.checked && totalAtual && totalItem) {
                    var total = totalAtual + totalItem;
                    totalElement.setAttribute('data-vl-total', total);
                    totalElement.value = total.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
                } else if (totalAtual > 0 && totalAtual && totalItem) {
                    var total = totalAtual - totalItem;
                    totalElement.setAttribute('data-vl-total', total);
                    totalElement.value = total.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
                }
            }

            if (qtd > 0) {
                var totalItemElement = document.querySelector("#total_item_" + index);
                var valorBase = parseFloat(totalItemElement.getAttribute('data-vl-base')) || 0;
                var valor = valorBase * qtd;
                totalItemElement.setAttribute('data-vl-atual', valor);
                totalItemElement.value = valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

                var tempoBase = document.querySelector("#tempo_item_" + index).getAttribute('data-vl-base');
                if (tempoBase) {
                    document.querySelector("#tempo_item_" + index).value = multiplicarTempo(tempoBase, qtd);
                }
            }
        });
    });

    document.querySelectorAll(".check_item").forEach(function (element) {
        element.addEventListener("change", function () {
            var index = element.getAttribute('data-index-check');

            var data1 = new Date(document.querySelector("#dt_ini").value);
            var data2 = new Date(document.querySelector("#dt_fim").value);

            var tempoOutros = 0;

            document.querySelectorAll("input:checked").forEach(function (checkedElement) {
                var index_f = checkedElement.getAttribute('data-index-check');
                if (index_f != index) {
                    tempoOutros += calcularMinutos(document.querySelector("#tempo_item_" + index_f).value);
                }
            });

            if (data1 && data2) {
                var diferenca = Math.abs(data2 - data1);
                var diferencaEmMinutos = Math.ceil(diferenca / 60000);
            }

            var minutos = calcularMinutos(document.querySelector("#tempo_item_" + index).value);

            if (diferencaEmMinutos >= (minutos + tempoOutros)) {
                var totalElement = document.querySelector("#total");
                var totalAtual = parseFloat(totalElement.getAttribute('data-vl-total')) || 0;
                var totalItem = parseFloat(document.querySelector("#total_item_" + index).getAttribute('data-vl-atual')) || 0;

                if (element.checked) {
                    document.querySelectorAll("input[data-index-check='" + index + "']").forEach(function (el) {
                        el.checked = true;
                    });
                    var total = totalAtual + totalItem;
                    totalElement.setAttribute('data-vl-total', total);
                    totalElement.value = total.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
                } else if (totalAtual > 0) {
                    document.querySelectorAll("input[data-index-check='" + index + "']").forEach(function (el) {
                        el.checked = false;
                    });
                    var total = totalAtual - totalItem;
                    totalElement.setAttribute('data-vl-total', total);
                    totalElement.value = total.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
                }
            } else {
                document.querySelectorAll("input[data-index-check='" + index + "']").forEach(function (el) {
                    el.checked = false;
                });
                mensagem("Quantidade informada passa do tempo máximo de agendamento");
                window.scroll({
                    top: 0,
                    left: 0,
                    behavior: "smooth",
                });
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {

    var url_base = window.location.href.split("/");
    url_base = url_base[0] + "//" + url_base[2] + "/";
    var url_atual = window.location.href;
    var qtd_bara = window.location.href.split("/").length;

    let cep = document.querySelector("input#cep");
    if (cep) {
        cep.addEventListener("blur", function () {
            let cepV = cep.value.replace(/[^0-9]/g, "");

            showLoader();

            fetch(url_base + "ajax", {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                method: 'POST',
                body: "method=getEndereco&parameters=" + cepV
            })
                .then(Result => Result.json())
                .then(json => {
                    if (json.sucesso) {

                        json = json.retorno;

                        let bairro = document.querySelector("input#bairro");
                        if (bairro) {
                            bairro.value = json.bairro;
                            bairro.focus();
                        }
                        let id_cidade = document.querySelector("select#id_cidade");
                        if (id_cidade) {
                            id_cidade.value = json.localidade
                            id_cidade.focus();
                        }
                        let id_estado = document.querySelector("select#id_estado");
                        if (id_estado) {
                            id_estado.value = json.uf
                            id_estado.focus();
                        }
                        let cep = document.querySelector("input#cep");
                        if (cep) {
                            cep.value = json.cep
                        }
                        let rua = document.querySelector("input#rua");
                        if (rua) {
                            rua.value = json.logradouro
                            rua.focus();
                        }
                        let numero = document.querySelector("input#numero");
                        if (numero) {
                            numero.focus();
                        }
                    }
                    else {
                        mensagem(json.retorno)
                    }

                    return;
                })
                .catch(errorMsg => { mensagem(errorMsg); });

            removeLoader();
        })
    }

    loadChoices();
    setEvents();

    document.body.addEventListener('htmx:xhr:loadstart', function (evt) {
        showLoader();
    });

    document.body.addEventListener('htmx:afterSettle', function (evt) {
        loadChoices();
        setEvents();
        removeLoader();
    });

    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.toggle('sb-sidenav-toggled');
        }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    htmx.config.globalViewTransitions = true;
    htmx.config.defaultFocusScroll = true;

    if (localStorage.getItem("avisoCookies") != "accept") {
        avisoCookies({
            message: 'Utilizamos cookies para que você tenha a melhor experiência em nosso site. Para saber mais acesse nossa página de <a href="\\privacidade">Política de Privacidade</a>'
        });
    }
});