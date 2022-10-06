// Avaa ja sulje menu
function openMenu() {
    let item = document.getElementById("mainMenu");
    if (item.className === "menu") {
        item.className += " opened";
    } else {
        item.className = "menu";
    }
}
