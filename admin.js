function showOutput(element, message, type = "info") {
    if (!element) return;
    element.classList.remove("hidden", "output-success", "output-error");
    element.classList.add(type === "error" ? "output-error" : "output-success");
    element.textContent = message;
}

function generateID(type) {
    const nameInput = document.getElementById("idName");
    const resultNode = document.getElementById("generatedResult");

    if (!nameInput || !resultNode) return;

    const name = nameInput.value.trim();
    if (name === "") {
        showOutput(resultNode, "Please enter a name first.", "error");
        nameInput.focus();
        return;
    }

    resultNode.textContent = "Generating ID...";
    resultNode.classList.remove("output-success", "output-error");

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
            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error("The server returned an invalid response. Check the PHP/database setup.");
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || "Unable to generate ID.");
            }

            const label = type === "student" ? "Student" : "Faculty";
            showOutput(
                resultNode,
                data.existing
                    ? `${label} ID already exists: ${data.id}`
                    : `${label} ID generated successfully: ${data.id}`
            );
        })
        .catch((error) => {
            console.error("ID generation failed:", error);
            showOutput(resultNode, error.message, "error");
        });
}

window.addEventListener("DOMContentLoaded", () => {
    const generateStudentBtn = document.getElementById("generateStudentBtn");
    const generateFacultyBtn = document.getElementById("generateFacultyBtn");
    const addScheduleBtn = document.getElementById("addScheduleBtn");
    const viewSchedulesBtn = document.getElementById("viewSchedulesBtn");
    const assignFacultyBtn = document.getElementById("assignFacultyBtn");
    const viewLogsBtn = document.getElementById("viewLogsBtn");

    generateStudentBtn?.addEventListener("click", () => generateID("student"));
    generateFacultyBtn?.addEventListener("click", () => generateID("faculty"));

    addScheduleBtn?.addEventListener("click", () => {
        showOutput(
            document.getElementById("scheduleOutput"),
            "Schedule form is ready. Connect this action to your schedule table/backend to save a new schedule."
        );
    });

    viewSchedulesBtn?.addEventListener("click", () => {
        showOutput(
            document.getElementById("scheduleOutput"),
            "No saved schedules are available yet. Add a schedule to display it here."
        );
    });

    assignFacultyBtn?.addEventListener("click", () => {
        const facultySelect = document.getElementById("facultySelect");
        const selectedFaculty = facultySelect?.value;

        showOutput(
            document.getElementById("facultyOutput"),
            selectedFaculty
                ? `${selectedFaculty} selected for assignment. Connect this action to your assignment table/backend to save it.`
                : "Please select a faculty member first.",
            selectedFaculty ? "info" : "error"
        );
    });

    viewLogsBtn?.addEventListener("click", () => {
        const logs = document.getElementById("activityLogs");
        const output = document.getElementById("logsOutput");

        if (!logs || !output) return;
        output.innerHTML = logs.innerHTML;
        output.classList.remove("hidden", "output-error");
        output.classList.add("output-success");
    });
});
