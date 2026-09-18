function selectRole(role) {
    const roleInput = document.getElementById("roleInput");
    const roleLabel = document.getElementById("roleLabel");
    const signupForm = document.getElementById("signupForm");
    const studentFields = document.getElementById("studentFields");
    const facultyFields = document.getElementById("facultyFields");

    roleInput.value = role;
    roleLabel.textContent =
        role.charAt(0).toUpperCase() + role.slice(1);

    document.querySelectorAll(".role-card").forEach(card => {
        card.classList.remove("active");
    });

    const selectedCard = document.getElementById("card-" + role);

    if (selectedCard) {
        selectedCard.classList.add("active");
    }

    signupForm.classList.remove("hidden");

    studentFields.classList.toggle("hidden", role !== "student");
    facultyFields.classList.toggle("hidden", role !== "faculty");

    document.querySelector(
        '#studentFields input[name="student_id"]'
    ).required = role === "student";

    document.querySelector(
        '#facultyFields input[name="faculty_id"]'
    ).required = role === "faculty";
}

function showSignup() {
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("signupPanel").classList.remove("hidden");
    document.getElementById("signupForm").classList.remove("hidden");
}

function showLogin() {
    document.getElementById("signupPanel").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
}

function togglePass() {
    const password = document.getElementById("pass");

    password.type =
        password.type === "password"
            ? "text"
            : "password";
}