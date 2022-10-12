// Avaa ja sulje menu
function openMenu() {
    let item = document.getElementById("mainMenu");
    if (item.className === "menu") {
        item.className += " opened";
    } else {
        item.className = "menu";
    }
}

function addAuthorField() {
    const count = document.getElementById("author-list").childElementCount;
    const author = document.getElementById("author-block" + (count - 1));
    let html = "<span class='author' id='author-block" + count + "'><input list='authors' name='author[]' id='author" + count + "'><i class='far fa-minus-square remove' onclick='removeAuthorField(\"author-block" + count + "\")'></i></span> ";
    author.insertAdjacentHTML("afterend", html);
}

function removeAuthorField(authorBlock) {
    const block = document.getElementById(authorBlock);
    block.remove();
}

function showPassword() {
    var x = document.getElementById("password");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}