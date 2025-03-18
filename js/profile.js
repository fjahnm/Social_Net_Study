function openTab(evt, tabName) {
    var i, tabContent, tabButtons;
    tabContent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = "none";
    }
    tabButtons = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tabButtons.length; i++) {
        tabButtons[i].className = tabButtons[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

// Add event listeners for delete post buttons
document.querySelectorAll('.delete-post-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Tem certeza que deseja excluir este post?')) {
            e.preventDefault();
        }
    });
});