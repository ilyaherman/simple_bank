window.onload = function() {
    var modal = document.getElementById("modalMessage");
    var closeBtn = document.getElementsByClassName("close")[0];
    var modalText = document.getElementById("modalText");

    var message = document.getElementById("modalMessage").getAttribute("data-message");
    if (message) {
        modalText.innerText = message;
        modal.style.display = "block";
    }

    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
};
