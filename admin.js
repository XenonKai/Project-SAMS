function generateID(type) {
    const nameInput = document.getElementById("idName");
    const resultNode = document.getElementById("generatedResult");

    if (!nameInput || !resultNode) {
        alert("This page is missing the ID generator fields.");
        return;
    }

    const name = nameInput.value.trim();

    if (name === "") {
        alert("Please enter a name first.");
        return;
    }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("type", type);

    fetch("generate_id.php", {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
        .then(async (response) => {
            const text = await response.text();

            try {
                const data = JSON.parse(text);

                if (!response.ok || !data.success) {
                    throw new Error(data.message || "Unable to generate ID.");
                }

                resultNode.innerText = "Generated ID: " + data.id;

                if (data.existing) {
                    alert("This person already has an ID.\n\nName: " + name + "\nID: " + data.id);
                } else {
                    alert("ID generated successfully!\n\nName: " + name + "\nID: " + data.id);
                }
            } catch (error) {
                console.error(error);
                alert("Something went wrong while generating the ID.");
            }
        })
        .catch((error) => {
            console.error(error);
            alert("Something went wrong.");
        });
}

window.addEventListener("DOMContentLoaded", () => {
    const generateStudentBtn = document.getElementById("generateStudentBtn");
    const generateFacultyBtn = document.getElementById("generateFacultyBtn");

    if (generateStudentBtn) {
        generateStudentBtn.addEventListener("click", function () {
            generateID("student");
        });
    }

    if (generateFacultyBtn) {
        generateFacultyBtn.addEventListener("click", function () {
            generateID("faculty");
        });
    }
});
