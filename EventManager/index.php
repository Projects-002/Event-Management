<?php

include 'db.php';





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container">


<table class="table table-bordered ">
  <thead>
    <tr>
      <th scope="col">Title</th>
      <th scope="col">Description</th>
      <th scope="col">Start Date</th>
      <th scope="col">Created at</th>
    </tr>
  </thead>
  <tbody>
<?php

$getevents = "SELECT * FROM events";
$feed = mysqli_query($conn, $getevents);

while($row = mysqli_fetch_assoc($feed)){
$title = $row['title'];
$description = $row['description'];
$start_datetime = $row['start_datetime'];
$end_datetime = $row['end_datetime'];
$created_at = $row['created_at'];


echo'
 <tr>
      <th scope="row">'.$title.'</th>
      <td>'.$description.'</td>
      <td>'.$start_datetime.'</td>
      <td>'.$created_at.'</td>
    </tr>

';

}
?>
    
  </tbody>
</table>

</div>
</body>
</html>

