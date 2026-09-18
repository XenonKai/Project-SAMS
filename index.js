function selectRole(role) {
    const roleInput = document.getElementById("roleInput");
    const roleLabel = document.getElementById("roleLabel");
    const signupForm = document.getElementById("signupForm");
    const studentFields = document.getElementById("studentFields");
    const facultyFields = document.getElementById("facultyFields");

    if (!roleInput || !roleLabel || !signupForm || !studentFields || !facultyFields) {
        return;
    }

    roleInput.value = role;
    roleLabel.textContent = role.charAt(0).toUpperCase() + role.slice(1);

    document.querySelectorAll(".role-card").forEach((card) => {
        card.classList.remove("active");
    });

    const selectedCard = document.getElementById("card-" + role);
    if (selectedCard) {
        selectedCard.classList.add("active");
    }

    signupForm.classList.remove("hidden");
    studentFields.classList.toggle("hidden", role !== "student");
    facultyFields.classList.toggle("hidden", role !== "faculty");

    const studentIdInput = document.querySelector('#studentFields input[name="student_id"]');
    const facultyIdInput = document.querySelector('#facultyFields input[name="faculty_id"]');

    if (studentIdInput) {
        studentIdInput.required = role === "student";
    }

    if (facultyIdInput) {
        facultyIdInput.required = role === "faculty";
    }
}

function showSignup() {
    const loginForm = document.getElementById("loginForm");
    const signupPanel = document.getElementById("signupPanel");
    const signupForm = document.getElementById("signupForm");

    if (loginForm) loginForm.classList.add("hidden");
    if (signupPanel) signupPanel.classList.remove("hidden");
    if (signupForm) signupForm.classList.remove("hidden");
}

function showLogin() {
    const signupPanel = document.getElementById("signupPanel");
    const loginForm = document.getElementById("loginForm");

    if (signupPanel) signupPanel.classList.add("hidden");
    if (loginForm) loginForm.classList.remove("hidden");
}

function togglePass() {
    const password = document.getElementById("pass");
    if (!password) return;

    password.type = password.type === "password" ? "text" : "password";
}
