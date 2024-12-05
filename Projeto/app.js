'use strict'

const switcher = document.querySelector('.btn');

switcher.addEventListener('click', function() {
    document.body.classList.toggle('tema-escuro');
    document.body.classList.toggle('tema-claro');

    var className = document.body.className;
    if (className === 'tema-claro') {
        this.textContent = 'Escuro';
    } else {
        this.textContent = 'Claro';
    }
});


const btnSobre = document.getElementById('btn-sobre');
const submenuSobre = document.getElementById('submenu-sobre');


btnSobre.addEventListener('click', function() {
    submenuSobre.classList.toggle('active');
});


const btnCadastrarEvento = document.getElementById('btn-campanhas');
const submenuCadastrarEvento = document.getElementById('submenu-campanhas');


btnCadastrarEvento.addEventListener('click', function() {
    submenuCadastrarEvento.classList.toggle('active');
});


const buttons = document.querySelectorAll('.menu-lateral button');
const menus = document.querySelectorAll('.conteudo-menu');


buttons.forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        console.log("Botão clicado:", targetId); 
        
        const targetMenu = document.getElementById(targetId);
        
        if (targetMenu) {
            
            menus.forEach(menu => menu.classList.remove('active'));
            targetMenu.classList.add('active');
        }
    });
});



const btnChat = document.querySelector('[data-target="menu-chat"]'); 
const menuChamados = document.getElementById('menu-chamados'); 


btnChat.addEventListener('click', function() {
    menuChamados.classList.toggle('hidden'); 
}); 

document.querySelectorAll('.btn-responder').forEach(button => {
    button.addEventListener('click', function() {
        const chamadoId = this.getAttribute('data-id');
        const respostaDiv = document.getElementById('resposta_' + chamadoId);
        respostaDiv.style.display = 'block'; 
    });
});


document.querySelectorAll('.btn-enviar-resposta').forEach(button => {
    button.addEventListener('click', function() {
        const chamadoId = this.getAttribute('data-id');
        const respostaTexto = document.getElementById('resposta-text-' + chamadoId).value;

    
        if (!respostaTexto.trim()) {
            alert("Por favor, digite uma resposta antes de enviar.");
            return;
        }

        const formData = new FormData();
        formData.append('id_chamado', chamadoId);
        formData.append('resposta', respostaTexto);

        fetch('responder_chamados.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Resposta enviada com sucesso!');
                
                document.getElementById('resposta_' + chamadoId).style.display = 'none';


                const respostaElement = document.createElement('div');
                respostaElement.classList.add('resposta');
                respostaElement.innerHTML = `<strong>Resposta do Administrador:</strong><p>${respostaTexto}</p>`;

                const chamadoDiv = document.getElementById('chamado_' + chamadoId);
                const respostaContainer = chamadoDiv.querySelector('.resposta-container');
                respostaContainer.appendChild(respostaElement); 


                chamadoDiv.querySelector('.status').innerText = 'Respondido';
            } else {
                alert('Erro ao enviar a resposta. Tente novamente.');
            }
        })
        .catch(error => {
            console.error('Erro ao enviar a resposta:', error);
            alert('Erro ao enviar a resposta. Tente novamente.');
        });
    });
});