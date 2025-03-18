function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            document.querySelector('#profile-info img').setAttribute('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);

     }
}

document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', function() {
        this.classList.toggle('liked');
    });
});

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        let textareas = this.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            if (textearea.value.trim() === '') {
                e.preventDefault();
                alert('Preencha todos os campos!');
            }
        });
    });
});

function loadMorePosts() {


}

document.addEventListener('DOMContentLoaded', function() {
    let profilePictureInput = document.querySelector('input[name="profile_picture"]');
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function() {
            previewProfilePicture(this);
        });
    }
});

// Função para enviar um voto via AJAX
function handleVote(postId, voteType) {
    fetch('vote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&vote_type=${voteType}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateVoteDisplay(postId, data.voteCount, data.userVote);
        } else {
            alert('Erro ao processar o voto. Por favor, tente novamente.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar o voto.');
    });
}

// Função para atualizar a exibição dos votos
function updateVoteDisplay(postId, voteCount, userVote) {
    const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
    const voteCountElement = postElement.querySelector('.vote-count');
    const upvoteButton = postElement.querySelector('.vote-button[value="upvote"]');
    const downvoteButton = postElement.querySelector('.vote-button[value="downvote"]');

    voteCountElement.textContent = voteCount;
    upvoteButton.classList.toggle('active', userVote === 'upvote');
    downvoteButton.classList.toggle('active', userVote === 'downvote');
}

// Adicionar eventos de clique para os botões de voto
document.querySelectorAll('.vote-button').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const postId = this.closest('.post').dataset.postId;
        const voteType = this.value;
        handleVote(postId, voteType);
    });
});