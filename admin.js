function generateID(type) {

    let name = document.getElementById("idName").value.trim();

    if (name == "") {

        alert("Please enter a name first.");
        return;

    }


    let formData = new FormData();

    formData.append("name", name);
    formData.append("type", type);


    fetch("generate_id.php", {

        method: "POST",
        body: formData

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            document.getElementById("generatedResult").innerText =
                "Generated ID: " + data.id;


            if (data.existing) {

                alert(
                    "This person already has an ID.\n\n" +
                    "Name: " + name + "\n" +
                    "ID: " + data.id
                );

            } else {

                alert(
                    "ID generated successfully!\n\n" +
                    "Name: " + name + "\n" +
                    "ID: " + data.id
                );

            }

        } else {

            alert(data.message);

        }

    })

    .catch(error => {

        console.log(error);

        alert("Something went wrong.");

    });

}


// Student ID
document.getElementById("generateStudentBtn").onclick = function () {

    generateID("student");

};


// Faculty ID
document.getElementById("generateFacultyBtn").onclick = function () {

    generateID("faculty");

};