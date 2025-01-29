

<?php

// index.php ve veřejném adresáři (public)
if ($_SERVER['REQUEST_URI'] === '/api.php') {
    include './api.php';  // Případně i celé routování dle potřeby
} else {
    echo "Welcome to the homepage!";
}


/*try {
    // Připojení k databázi (nezapomeň změnit na správné hodnoty)
    $pdo = new PDO('mysql:host=localhost;dbname=e_stezka', 'root', '3st3zkaSQL!');
    // Nastavení režimu pro zobrazení chyb
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Databáze připojena!<br>";  // Úspěšné připojení
} catch (PDOException $e) {
    // Chyba připojení
    echo "Chyba připojení: " . $e->getMessage();
}*/

/*try {
    // Připojení k databázi (nezapomeň změnit na správné hodnoty)
    $pdo = new PDO('mysql:host=localhost;dbname=e_stezka', 'root', '3st3zkaSQL!');
    // Nastavení režimu pro zobrazení chyb
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL dotaz pro výběr všech řádků z tabulky task
    $query = "SELECT * FROM task";

    // Příprava a vykonání dotazu
    $stmt = $pdo->query($query);

    // Pokud jsou nějaké výsledky, vypiš je
    if ($stmt->rowCount() > 0) {
        // Projdeme všechny řádky a vypíšeme je
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: " . $row['id_task'] . "<br>";
            echo "Number: " . $row['number'] . "<br>";
            echo "Name: " . $row['name'] . "<br>";
            echo "Description: " . $row['description'] . "<br>";
            echo "Category: " . $row['category'] . "<br>";
            echo "Tag: " . $row['tag'] . "<br><hr>";
        }
    } else {
        echo "Žádná data v tabulce.";
    }

} catch (PDOException $e) {
    // Chyba připojení nebo dotazu
    echo "Chyba připojení: " . $e->getMessage();
}*/

/*// Zobrazíme výchozí zprávu
echo "PHP API server is running!<br>";

// Voláme API a získáme data ve formátu JSON
$apiUrl = 'http://localhost:8000/api.php';  // Adresa tvého API
$response = file_get_contents($apiUrl);  // Načteme odpověď z API

// Převedeme JSON odpověď na pole
$data = json_decode($response, true);

// Pokud jsou data k dispozici, vypíšeme je
if ($data) {
    echo "<h3>Tasks from the Database:</h3>";
    echo "<ul>";
    foreach ($data as $task) {
        echo "<li>" . $task['name'] . " - " . $task['description'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "No tasks found.";
}

*/?>