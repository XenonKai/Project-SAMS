function showOutput(element, message, type = "info") {
    if (!element) return;
    element.classList.remove("hidden", "output-success", "output-error");
    element.classList.add(type === "error" ? "output-error" : "output-success");
    element.textContent = message;
}

async function readJsonResponse(response) {
    const text = await response.text();
    let data;

    try {
        data = JSON.parse(text);
    } catch (error) {
        // A response beginning with <!DOCTYPE usually means a PHP/server error page
        // or a redirect was returned instead of the API JSON response.
        if (text.trim().startsWith("<!DOCTYPE") || text.trim().startsWith("<html")) {
            throw new Error("The server returned an HTML error page instead of JSON. Check the PHP error log and make sure create_schedule.php is deployed.");
        }
        throw new Error(text.trim() || "The server returned an empty response.");
    }

    if (!response.ok || !data.success) {
        throw new Error(data.message || "The request could not be completed.");
    }

    return data;
}

function formatSchedule(schedule) {
    const date = new Date(`${schedule.schedule_date}T00:00:00`);
    const formattedDate = Number.isNaN(date.getTime())
        ? schedule.schedule_date
        : date.toLocaleDateString(undefined, { month: "short", day: "numeric", year: "numeric" });
    return `${formattedDate} • ${schedule.start_time.slice(0, 5)}-${schedule.end_time.slice(0, 5)} • ${schedule.subject} • ${schedule.course} ${schedule.section}${schedule.room ? ` • Room ${schedule.room}` : ""}${schedule.faculty ? ` • ${schedule.faculty}` : ""}`;
}

async function loadSchedules() {
    const output = document.getElementById("scheduleOutput");
    if (!output) return;
    showOutput(output, "Loading schedules...");

    try {
        const response = await fetch("list_schedules.php", { headers: { "X-Requested-With": "XMLHttpRequest" } });
        const data = await readJsonResponse(response);
        output.innerHTML = "";
        output.classList.remove("hidden", "output-error");
        output.classList.add("output-success");

        if (!data.schedules.length) {
            output.textContent = "No saved schedules yet.";
            return;
        }

        const title = document.createElement("strong");
        title.textContent = "Saved schedules";
        output.appendChild(title);
        const list = document.createElement("ul");
        data.schedules.forEach((schedule) => {
            const item = document.createElement("li");
            item.textContent = formatSchedule(schedule);
            list.appendChild(item);
        });
        output.appendChild(list);
    } catch (error) {
        showOutput(output, error.message, "error");
    }
}

function generateID(type) {
    const nameInput = document.getElementById("idName");
    const resultNode = document.getElementById("generatedResult");
    if (!nameInput || !resultNode) return;
    const name = nameInput.value.trim();

    if (!name) {
        showOutput(resultNode, "Please enter a name first.", "error");
        nameInput.focus();
        return;
    }

    showOutput(resultNode, "Generating ID...");
    const formData = new FormData();
    formData.append("name", name);
    formData.append("type", type);

    fetch("generate_id.php", { method: "POST", body: formData, headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(readJsonResponse)
        .then((data) => {
            const label = type === "student" ? "Student" : "Faculty";
            showOutput(resultNode, data.existing ? `${label} ID already exists: ${data.id}` : `${label} ID generated successfully: ${data.id}`);
        })
        .catch((error) => showOutput(resultNode, error.message, "error"));
}

window.addEventListener("DOMContentLoaded", () => {
    const scheduleModal = document.getElementById("scheduleModal");
    const scheduleForm = document.getElementById("scheduleForm");
    const scheduleFormOutput = document.getElementById("scheduleFormOutput");

    document.getElementById("generateStudentBtn")?.addEventListener("click", () => generateID("student"));
    document.getElementById("generateFacultyBtn")?.addEventListener("click", () => generateID("faculty"));
    document.getElementById("addScheduleBtn")?.addEventListener("click", () => scheduleModal?.classList.remove("hidden"));
    document.getElementById("viewSchedulesBtn")?.addEventListener("click", loadSchedules);
    document.getElementById("closeScheduleBtn")?.addEventListener("click", () => scheduleModal?.classList.add("hidden"));
    scheduleModal?.addEventListener("click", (event) => {
        if (event.target === scheduleModal) scheduleModal.classList.add("hidden");
    });

    scheduleForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        showOutput(scheduleFormOutput, "Saving schedule...");

        try {
            const response = await fetch("create_schedule.php", {
                method: "POST",
                body: new FormData(scheduleForm),
                headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
            });
            const data = await readJsonResponse(response);
            showOutput(scheduleFormOutput, data.message);
            scheduleForm.reset();
            await loadSchedules();
        } catch (error) {
            console.error("Schedule creation failed:", error);
            showOutput(scheduleFormOutput, error.message, "error");
        }
    });

    document.getElementById("assignFacultyBtn")?.addEventListener("click", () => {
        const selected = document.getElementById("facultySelect")?.value;
        showOutput(document.getElementById("facultyOutput"), selected ? `${selected} selected for assignment.` : "Please select a faculty member first.", selected ? "info" : "error");
    });

    document.getElementById("viewLogsBtn")?.addEventListener("click", () => {
        const logs = document.getElementById("activityLogs");
        const output = document.getElementById("logsOutput");
        if (!logs || !output) return;
        output.innerHTML = logs.innerHTML;
        output.classList.remove("hidden", "output-error");
        output.classList.add("output-success");
    });
});
