function showMasterlist() {

    const masterlist =
        document.getElementById("masterlist");

    masterlist.classList.toggle("hidden");

}


function showAdvisory() {

    alert(
        "Advisory Attendance\n\n" +
        "BSCS 2A\n\n" +
        "Present: 30\n" +
        "Late: 2\n" +
        "Absent: 3"
    );

}