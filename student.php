<?php
$conn = connect();
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'sid';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$search = isset($_GET['search']) ? $_GET['search'] : null;

switch (isset($_GET['is']) ? $_GET['is'] : '') {
    case 'sil':
        echo pageHeader("Student List");
        sil($_GET['sid']);
        listele($page, $sort, $order, $search);
        break;
    case 'eklemeFormu':
        eklemeFormu();
        break;
    case 'ekle':
        echo pageHeader("Student List");
        ekle($_GET['name'], $_GET['surname'], $_GET['dogumYeri'], $_GET['dogumTarihi']);
        listele($page, $sort, $order, $search);
        break;
    case 'degistirmeFormu':
        echo pageHeader("Update Information");
        form($_GET['sid']);
        break;
    case 'guncelle':
        echo pageHeader("Student List");
        guncelle($_GET['sid'], $_GET['name'], $_GET['surname'], $_GET['dogumYeri'], $_GET['dogumTarihi']);
        listele($page, $sort, $order, $search);
        break;
    default:
        echo pageHeader("Student List");
        listele($page, $sort, $order, $search);
}

function eklemeFormu()
{
    echo '
    <form style="margin-top:50px"  method="GET">
        <input type="hidden" name="is" value="ekle">
        <label for="isim">İsim:</label>
        <input type="text" name="name" required><br><br>
        <label for="soyisim">Soyisim:</label>
        <input type="text" name="surname" required><br><br>
        <label for="dogumYeri">Doğum Yeri:</label>
        <input type="text" name="dogumYeri" required><br><br>
        <label for="dogumTarihi">Doğum Tarihi:</label>
        <input type="date" name="dogumTarihi" required><br><br>
        <button type="submit" class="add-button">Ekle</button>
        <button type="reset" class="add-button">Reset</button>
    </form>';
}

function connect()
{
    $servername = "localhost";
    $username = "mustafa";
    $password = "1234";
    $dbname = "sdb";

    $conn = new mysqli($servername, $username, $password, $dbname) or die("Connection failed: " . $conn->connect_error);
    return $conn;
}

function pageHeader($heading)
{
    echo "<html><head><title>$heading</title></head><body>";
}

function pageFooter()
{
    echo "</body></html>";
}

function buildUrl($extraQueries = []){
    global $search, $sort, $order, $page;
    $params = [];
    if ($search) {
        $params['search'] = $search;
    }
    if ($sort && $sort !== 'sid') {
        $params['sort'] = $sort;
    }
    if ($order && $order !== "ASC") {
        $params['order'] = $order;
    }
    if ($page && $page > 1) {
        $params['page'] = $page;
    }
    foreach ($extraQueries as $key => $value) {
        $params[$key] = $value;
    }
    return http_build_query($params);
}

function listele($page, $sort, $order, $search)
{
    $buildUrl = 'buildUrl';
    global $conn;
    $limit = 5;
    $totalRecords = mysqli_query($conn, "SELECT COUNT(*) AS total FROM studentdb");
    $totalRecords = mysqli_fetch_assoc($totalRecords)['total'];
    $totalPages = ceil($totalRecords / $limit);
    $prevPage = max($page - 1, 1);
    $nextPage = min($page + 1, $totalPages);
    

    $urlQuery = "?";
    $query = 'SELECT * from studentdb ';

    if ($search) {
        echo "<p>Aradığınız kelimeyi : <strong>$search</strong></p>";
        $query = $query . "WHERE fname LIKE '%" . $search . "%' OR lname LIKE '%" . $search . "%' OR birthPlace LIKE '%" . $search . "%' ";
        $urlQuery = $urlQuery . "&search=$search";
    }
    if ($sort) {
        $query = $query . 'ORDER BY ' . $sort . ' ' . $order . ' ';
        $urlQuery = $urlQuery . "&sort=$sort";
    }
    if ($page) {
        $limit = 5;
        $offset = ($page - 1) * $limit;
        $query = $query . 'LIMIT ' . $limit . ' OFFSET ' . $offset . ' ';
        // $urlQuery = $urlQuery . "&page=$page";
    }
    $query = $query . ";";

    $kayitKumesi = mysqli_query($conn, $query) or die(mysqli_error($conn));

    echo "<style>td {border:1px solid red}</style>
    <form  method='GET'>
        <input type='text' name='search'>
        <button>Bul</button>
        <button>Temizle</button>
    </form>
    <table>
        <tr>
            <td><a href='?{$buildUrl(['sort'=> 'sid', 'order' => $order === 'ASC' ? 'DESC' : 'ASC'])}'>NO</a></td>
            <td><a href='?{$buildUrl(['sort'=> 'fname', 'order' => $order === 'ASC' ? 'DESC' : 'ASC'])}'>AD</a></td>
            <td><a href='?{$buildUrl(['sort'=> 'lname', 'order' => $order === 'ASC' ? 'DESC' : 'ASC'])}'>SOYAD</a></td>
            <td><a href='?{$buildUrl(['sort'=> 'birthPlace', 'order' => $order === 'ASC' ? 'DESC' : 'ASC'])}'>DOĞUM YERİ</a></td>
            <td><a href='?{$buildUrl(['sort'=> 'birthDate', 'order' => $order === 'ASC' ? 'DESC' : 'ASC'])}'>DOĞUM TARİHİ</a></td>
            <td><a href='?is=eklemeFormu'>Yeni öğrenci ekle</a></td>
        </tr>";
    while ($kayit = mysqli_fetch_assoc($kayitKumesi)) {
        echo "<tr>
            <td>{$kayit['sid']}</td>
            <td>{$kayit['fname']}</td>
            <td>{$kayit['lname']}</td>
            <td>{$kayit['birthPlace']}</td>
            <td>{$kayit['birthDate']}</td>
            <td><a href='?is=sil&page={$page}&sid={$kayit['sid']}'>Sil</a></td>
            <td><a href='?is=degistirmeFormu&sid={$kayit['sid']}'>Güncelle</a></td>
        </tr>\n";
    }
    echo "</table>";

    echo '<div style="margin-left:200px; margin-top:20px; font-size:larger">';
    if ($page != 1) {
        echo "<div><a href='?{$buildUrl(['page' => 1])}'>&lt;&lt;</a> ";
        echo "<a href='?{$buildUrl(['page' => $prevPage])}'>&lt;</a>  ";
    }
    for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++) {
        echo "<a href='?{$buildUrl(['page' => $i])}'>$i</a> ";
    }
    if ($page != $totalPages) {
        echo "<a href='?{$buildUrl(['page' => $nextPage])}'>&gt;</a>  ";
        echo "<a href='?{$buildUrl(['page' => $totalPages])}'>&gt;&gt;</a></div>";
    }
    echo '</div>';
    mysqli_close($conn) or die(mysqli_error($conn));
}

function sil($sid)
{
    global $conn;
    $conn = connect();
   
    $query = "DELETE FROM studentdb WHERE sid='$sid'";
    $retval = mysqli_query($conn, $query) or die("Delete operation failed");

}

function ekle($name, $surname, $dogumYeri, $dogumTarihi)
{
    global $conn;

    $name = mysqli_real_escape_string($conn, $name);
    $surname = mysqli_real_escape_string($conn, $surname);
    $dogumYeri = mysqli_real_escape_string($conn, $dogumYeri);
    $dogumTarihi = mysqli_real_escape_string($conn, $dogumTarihi);

    $query = "INSERT INTO studentdb (fname, lname, birthPlace, birthDate) VALUES ('$name', '$surname', '$dogumYeri', '$dogumTarihi')";
    $retval = mysqli_query($conn, $query) or die("Insert operation failed");
}

function form($sid)
{
    global $conn;
    $conn = connect();

    $sid = mysqli_real_escape_string($conn, $sid);
    $result = mysqli_query($conn, "SELECT * FROM studentdb WHERE sid='$sid'");
    $student = mysqli_fetch_assoc($result);

    echo '
    <form  method="GET">
        <input type="hidden" name="is" value="guncelle">
        <input hidden name="sid" value="' . $sid . '">
        <label for="isim">İsim:</label>
        <input type="text" name="name" value="' . $student['fname'] . '" required><br><br>
        <label for="soyisim">Soyisim:</label>
        <input type="text" name="surname" value="' . $student['lname'] . '" required><br><br>
        <label for="dogumYeri">Doğum Yeri:</label>
        <input type="text" name="dogumYeri" value="' . $student['birthPlace'] . '" required><br><br>
        <label for="dogumTarihi">Doğum Tarihi:</label>
        <input type="date" name="dogumTarihi" value="' . $student['birthDate'] . '" required><br><br>
        <button type="submit" class="update-button">Güncelle</button>
    </form>';
}

function guncelle($sid, $name, $surname, $dogumYeri, $dogumTarihi)
{
    global $conn;

    $sid = mysqli_real_escape_string($conn, $sid);
    $name = mysqli_real_escape_string($conn, $name);
    $surname = mysqli_real_escape_string($conn, $surname);
    $dogumYeri = mysqli_real_escape_string($conn, $dogumYeri);
    $dogumTarihi = mysqli_real_escape_string($conn, $dogumTarihi);

    $query = "UPDATE studentdb SET fname='$name', lname='$surname', birthPlace='$dogumYeri', birthDate='$dogumTarihi' WHERE sid='$sid'";
    $retval = mysqli_query($conn, $query) or die("Update operation failed");
}

?>
