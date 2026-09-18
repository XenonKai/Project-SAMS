function showOutput(element, message, type = "info") {
    if (!element) return;
    element.classList.remove("hidden", "output-success", "output-error");
    element.classList.add(type === "error" ? "output-error" : "output-success");
    element.textContent = message;
}

async function jsonRequest(url, options) {
    const response = await fetch(url, options);
    const text = await response.text();
    let data;
    try { data = JSON.parse(text); } catch { throw new Error("The server returned an HTML/error page instead of JSON."); }
    if (!response.ok || !data.success) throw new Error(data.message || "Request failed.");
    return data;
}

function formatSchedule(schedule) {
    const faculty = schedule.faculty ? ` • Faculty: ${schedule.faculty}` : " • Faculty: Unassigned";
    return `${schedule.schedule_date} • ${schedule.start_time.slice(0, 5)}-${schedule.end_time.slice(0, 5)} • ${schedule.subject} • ${schedule.course} ${schedule.section}${faculty}${schedule.room ? ` • Room ${schedule.room}` : ""}`;
}

async function loadSchedules() {
    const output = document.getElementById("scheduleOutput");
    showOutput(output, "Loading schedules...");
    try {
        const data = await jsonRequest("list_schedules.php", { headers: { "X-Requested-With": "XMLHttpRequest" } });
        output.innerHTML = data.schedules.length
            ? `<strong>Saved schedules</strong><ul>${data.schedules.map(schedule => `<li>${formatSchedule(schedule)}</li>`).join("")}</ul>`
            : "No saved schedules yet.";
        output.classList.remove("hidden");
    } catch (error) {
        showOutput(output, error.message, "error");
    }
}

function toggleAccountFields() {
    const role = document.getElementById("accountRole")?.value;
    document.getElementById("studentAccountFields")?.classList.toggle("hidden", role !== "student");
    document.getElementById("facultyAccountFields")?.classList.toggle("hidden", role !== "faculty");
}

window.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("scheduleModal");
    const scheduleForm = document.getElementById("scheduleForm");
    const scheduleFormOutput = document.getElementById("scheduleFormOutput");
    const accountForm = document.getElementById("createUserForm");
    const accountOutput = document.getElementById("accountOutput");

    document.getElementById("accountRole")?.addEventListener("change", toggleAccountFields);
    accountForm?.addEventListener("submit", async event => {
        event.preventDefault();
        showOutput(accountOutput, "Creating account and generating password...");
        try {
            const data = await jsonRequest("create_user.php", { method: "POST", body: new FormData(accountForm), headers: { "Accept": "application/json" } });
            accountOutput.innerHTML = `<strong>Save these credentials now</strong><br>Account ID: ${data.account_id}<br>Email: ${data.email}<br>Temporary password: <b>${data.password}</b><br><small>This password is shown only once.</small>`;
            accountOutput.classList.remove("hidden", "output-error");
            accountForm.reset();
            toggleAccountFields();
        } catch (error) {
            showOutput(accountOutput, error.message, "error");
        }
    });

    document.getElementById("addScheduleBtn")?.addEventListener("click", () => modal?.classList.remove("hidden"));
    document.getElementById("viewSchedulesBtn")?.addEventListener("click", loadSchedules);
    document.getElementById("closeScheduleBtn")?.addEventListener("click", () => modal?.classList.add("hidden"));
    modal?.addEventListener("click", event => { if (event.target === modal) modal.classList.add("hidden"); });

    scheduleForm?.addEventListener("submit", async event => {
        event.preventDefault();
        showOutput(scheduleFormOutput, "Saving schedule...");
        try {
            const data = await jsonRequest("create_schedule.php", { method: "POST", body: new FormData(scheduleForm), headers: { "Accept": "application/json" } });
            showOutput(scheduleFormOutput, `${data.message} Faculty assigned successfully.`);
            scheduleForm.reset();
            await loadSchedules();
        } catch (error) {
            showOutput(scheduleFormOutput, error.message, "error");
        }
    });

    document.getElementById("viewLogsBtn")?.addEventListener("click", () => {
        const logs = document.getElementById("activityLogs");
        const output = document.getElementById("logsOutput");
        if (logs && output) {
            output.innerHTML = logs.innerHTML;
            output.classList.remove("hidden");
        }
    });
});
