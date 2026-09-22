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

function updateCourseOptions() {
    const yearLevel = document.getElementById("yearLevelSelect")?.value;
    const courseSelect = document.getElementById("courseSelect");
    if (!courseSelect) return;
    const selectedLevel = yearLevel === "Grade 11" || yearLevel === "Grade 12" ? "shs"
        : ["1st Year", "2nd Year", "3rd Year", "4th Year"].includes(yearLevel) ? "college" : "";
    Array.from(courseSelect.options).forEach(option => {
        if (!option.dataset.level) option.hidden = false;
        else option.hidden = selectedLevel !== "" && option.dataset.level !== selectedLevel;
    });
    const selectedOption = courseSelect.selectedOptions[0];
    if (selectedOption?.dataset.level && selectedOption.dataset.level !== selectedLevel) courseSelect.value = "";
    courseSelect.required = Boolean(selectedLevel);
}

const facultyCourses = {
    "Science, Technology, Engineering, and Mathematics (STEM)": ["STEM", "ICT", "BSCS", "BSEN", "ACT"],
    "Humanities & Arts": [],
    "Social & Behavioral Sciences": [],
    "Law, Public Safety, & Governance": ["HUMSS", "GAS"],
    "Business & Management": ["ABM", "BSAIS", "GAS"],
    "Health & Medical Sciences": ["STEM", "BSEN", "GAS"],
    "Information Technology (IT) Services": ["ACT", "BSCS", "BSAIS", "ICT", "STEM", "GAS"],
    "Campus Safety & Security": ["HUMSS", "GAS", "ICT"],
    "Student Affairs & Auxiliary Services": ["HUMSS", "ABM", "ICT", "GAS"],
    "Finance & Corporate Administration": ["ABM", "BSAIS", "HUMSS", "BSCS", "GAS"],
    "Facilities & Estates Management": ["BSEN", "STEM", "ICT", "ABM", "GAS"]
};

function updateFacultyTeachingCourses() {
    const expertise = document.getElementById("facultyExpertiseSelect")?.value;
    const courseSelect = document.getElementById("facultyTeachingCourseSelect");
    if (!courseSelect) return;
    const courses = facultyCourses[expertise] || [];
    courseSelect.replaceChildren(new Option(courses.length ? "Select strand / course taught" : "No strand/course listed", ""));
    courses.forEach(course => courseSelect.add(new Option(course, course)));
    courseSelect.disabled = courses.length === 0;
    courseSelect.required = courses.length > 0;
    courseSelect.value = "";
}

function toggleAccountFields() {
    const role = document.getElementById("accountRole")?.value;
    document.getElementById("studentAccountFields")?.classList.toggle("hidden", role !== "student");
    document.getElementById("facultyAccountFields")?.classList.toggle("hidden", role !== "faculty");
    updateCourseOptions();
    updateFacultyTeachingCourses();
}

window.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("scheduleModal");
    const scheduleForm = document.getElementById("scheduleForm");
    const scheduleFormOutput = document.getElementById("scheduleFormOutput");
    const accountForm = document.getElementById("createUserForm");
    const accountOutput = document.getElementById("accountOutput");
    document.getElementById("accountRole")?.addEventListener("change", toggleAccountFields);
    document.getElementById("yearLevelSelect")?.addEventListener("change", updateCourseOptions);
    document.getElementById("facultyExpertiseSelect")?.addEventListener("change", updateFacultyTeachingCourses);
    updateCourseOptions();
    updateFacultyTeachingCourses();
    accountForm?.addEventListener("submit", async event => {
        event.preventDefault();
        showOutput(accountOutput, "Creating account and generating password...");
        try {
            const data = await jsonRequest("create_user.php", { method: "POST", body: new FormData(accountForm), headers: { "Accept": "application/json" } });
            accountOutput.innerHTML = `<strong>Save these credentials now</strong><br>Account ID: ${data.account_id}<br>Email: ${data.email}<br>Temporary password: <b>${data.password}</b>`;
            accountOutput.classList.remove("hidden", "output-error");
            accountForm.reset();
            toggleAccountFields();
        } catch (error) { showOutput(accountOutput, error.message, "error"); }
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
        } catch (error) { showOutput(scheduleFormOutput, error.message, "error"); }
    });
    document.getElementById("viewLogsBtn")?.addEventListener("click", () => {
        const logs = document.getElementById("activityLogs");
        const output = document.getElementById("logsOutput");
        if (logs && output) { output.innerHTML = logs.innerHTML; output.classList.remove("hidden"); }
    });
});
