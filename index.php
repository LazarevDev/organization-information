<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Поиск организаций</title>
</head>
<body>
    <header>
        <div class="container">
            <div class="headerContent">
                <div class="headerTitle">
                    <h1>Поиск информации <br> об организациях (ООО и ИП)</h1>
                    <p>Получите полные сведения об организациях и ИП для проверки <br> надежности и принятия взвешенных решений.</p>
                </div>

                <div class="formContainer">
                    <form action="" class="form">
                        <input type="text" id="organizationInput" class="input" placeholder="Введите Название или ИНН">
                        <div class="suggestions" id="suggestions"></div>
                    </form>
                </div>
               
            </div>
        </div>

        <div class="mask"></div>
    </header>

    <section>
        <div class="container">
            <div id="organizationDetails" class="organizationDetails hidden"></div>

        </div>
    </section>


    <script>
        const organizationInput = document.getElementById("organizationInput");
        const suggestions = document.getElementById("suggestions");
        const organizationDetails = document.getElementById("organizationDetails");

        organizationInput.addEventListener("input", function () {
            const query = this.value.trim();
            if (query.length > 2) {
                suggestions.style.display = 'block';
                fetch("party_autocomplete.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ query: query }),
                })
                .then((response) => response.json())
                .then((data) => {
                    suggestions.innerHTML = "";
                    if (Array.isArray(data.suggestions)) {
                        data.suggestions.forEach((item) => {
                            const suggestion = document.createElement("div");
                            suggestion.textContent = item.value;
                            suggestion.addEventListener("click", () => {
                                organizationInput.value = item.value;
                                suggestions.innerHTML = "";
                                console.log(item);
                                fetchOrganizationDetails(item.id);
                                suggestions.style.display = 'none';
                            });
                            suggestions.appendChild(suggestion);
                        });
                    } else {
                        suggestions.innerHTML = "<div>Ничего не найдено</div>";
                    }
                })
                .catch((error) => {
                    console.error("Ошибка:", error);
                    suggestions.innerHTML = "<div>Ошибка запроса</div>";
                });
        } else {
            suggestions.innerHTML = "";
        }
});

function fetchOrganizationDetails(id) {
    fetch("organization_details.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: id }),
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.success) {
            const { name, inn, ogrn, address, status } = data.details;
            organizationDetails.innerHTML = `
                <h2>${name}</h2>
                <p><strong>ИНН:</strong> ${inn}</p>
                <p><strong>ОГРН:</strong> ${ogrn}</p>
                <p><strong>Адрес:</strong> ${address}</p>
                <p><strong>Статус:</strong> ${status}</p>
            `;
            organizationDetails.classList.remove("hidden");
        } else {
            organizationDetails.innerHTML = "<p>Информация об организации не найдена.</p>";
            organizationDetails.classList.remove("hidden");
        }
    })
    .catch((error) => {
        console.error("Ошибка:", error);
        organizationDetails.innerHTML = "<p>Ошибка загрузки данных об организации.</p>";
        organizationDetails.classList.remove("hidden");
    });
}
    </script>
</body>
</html>