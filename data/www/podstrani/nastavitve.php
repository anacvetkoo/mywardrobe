<?php
require_once "../includes/session.php";
include "../includes/header.php";
?>

<div class="container my-5">
    <h1 class="mb-4">Nastavitve</h1>

    <!-- DARK MODE -->
    <div class="mb-4">
        <h5>Način prikaza</h5>

        <div class="form-check form-switch">
            <input
                class="form-check-input"
                type="checkbox"
                id="darkModeToggle"
                checked
            >
            <label class="form-check-label" for="darkModeToggle">
                Temni način (Dark mode)
            </label>
        </div>
    </div>

    <!-- VELIKOST KARTIC -->
    <div class="mb-4">
        <h5>Velikost kartic izdelkov</h5>

        <div class="form-check">
            <input
                class="form-check-input"
                type="radio"
                name="cardSize"
                id="cardLarge"
                value="large"
            >
            <label class="form-check-label" for="cardLarge">
                Velike ikone
            </label>
        </div>

        <div class="form-check">
            <input
                class="form-check-input"
                type="radio"
                name="cardSize"
                id="cardSmall"
                value="small"
                checked
            >
            <label class="form-check-label" for="cardSmall">
                Majhne ikone
            </label>
        </div>
    </div>
</div>

<script>
/* ===== DARK MODE ===== */
const darkToggle = document.getElementById("darkModeToggle");
const savedTheme = localStorage.getItem("theme") ?? "dark";

if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
    darkToggle.checked = true;
} else {
    document.body.classList.remove("dark-mode");
    darkToggle.checked = false;
}

darkToggle.addEventListener("change", () => {
    if (darkToggle.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
    } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
    }
});

/* ===== CARD SIZE ===== */
const savedSize = localStorage.getItem("cardSize") ?? "small";
document.getElementById(savedSize === "large" ? "cardLarge" : "cardSmall").checked = true;

document.querySelectorAll('input[name="cardSize"]').forEach(radio => {
    radio.addEventListener("change", () => {
        localStorage.setItem("cardSize", radio.value);
    });
});
</script>

<?php include "../includes/footer.php"; ?>
