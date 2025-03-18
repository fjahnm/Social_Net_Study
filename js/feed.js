document.addEventListener('DOMContentLoaded', function () {
    // Capturar os botões de voto
    const voteButtons = document.querySelectorAll('.vote-button');

    voteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const postId = button.closest('.vote-form').dataset.postId;
            const voteType = button.dataset.vote;

            // Enviar os votos via AJAX
            fetch('vote.php', {
                method: 'POST',
                body: new URLSearchParams({
                    post_id: postId,
                    vote_type: voteType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar o contador de votos
                    const voteCountElement = button.closest('.vote-form').querySelector('.vote-count');
                    voteCountElement.textContent = data.new_vote_count;

                    // Atualizar o estado dos botões (ativar o botão de voto)
                    const allButtons = button.closest('.vote-form').querySelectorAll('.vote-button');
                    allButtons.forEach(b => b.classList.remove('active'));
                    button.classList.add('active');
                } else {
                    alert('Erro ao votar.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar o voto.');
            });
        });
    });
});