<style>
  @import url("https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800");

  html {
    font-family: "Nunito Sans", sans-serif;
  }

  body {
    background-color: #ebebeb;
    line-height: 1.5;
  }

  .card {
    background-color: #ffffff;
    flex: 1 1 auto;
    padding: 1.25rem;
    margin: 1.25rem;
  }

  .btn {
    color: #fff;
    background-color: #2255a4;
    border-color: #2255a4;
    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 2px;
  }
</style>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<form action="/syn/index.php" method="post">
  <div class="card">
    <strong>Search</strong>
    <input type="text" name="search"/>
    <strong>Date</strong>
    <input type="date" name="date1"/>-<input type="date" name="date2"/>
    <br>
    <strong>Media</strong>
    Newspaper<input type="checkbox" value="'1'" name="media[]">
    TV<input type="checkbox" value="'2'" name="media[]">
    Radio<input type="checkbox" value="'3'" name="media[]">
    Website<input type="checkbox" value="'4'" name="media[]">
    Facebook<input type="checkbox" value="'5'" name="media[]">
    Twitter<input type="checkbox" value="'6'" name="media[]">
    Pantip<input type="checkbox" value="'7'" name="media[]">
    Youtube<input type="checkbox" value="'8'" name="media[]">
    Instagram<input type="checkbox" value="'9'" name="media[]">
    <br>
    <strong>Tone</strong>
    Negative<input type="checkbox" value="'1'" name="tone[]">
    Neutral<input type="checkbox" value="'2'" name="tone[]">
    Positive<input type="checkbox" value="'3'" name="tone[]">
    <br>
    <strong>Order by</strong>
    Tone<input type="radio" value=" ORDER BY media_lv ASC" name="sort[]">
    Header<input type="radio" value=" ORDER BY haed ASC" name="sort[]">
    Type<input type="radio" value=" ORDER BY media_type ASC" name="sort[]">
    Date<input type="radio" value=" ORDER BY date DESC" name="sort[]" checked="checked">
    Source<input type="radio" value=" ORDER BY media ASC" name="sort[]">
    <br>
    <button type="submit" value="Search" class="btn" style="display: inline;"><span class="material-icons" style="font-size: 20px;">search</span>Search</button>
  </div>

  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>

  <div class="card">
    <table>
      <tr>
        <td colspan="2">
          <figure class="highcharts-figure">
              <div id="lineChartTone"></div>
          </figure>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <figure class="highcharts-figure">
              <div id="barChartType"></div>
          </figure>
        </td>
      </tr>
      <tr>
        <td>
          <figure class="highcharts-figure">
              <div id="pieChartTone"></div>
          </figure>
        </td>
        <td>
          <figure class="highcharts-figure">
              <div id="pieChartType"></div>
          </figure>
        </td>
      </tr>
    </table>
  </div>

</form>



<?php 

// Details for connection
$server = '';	//**INSERT SERVER NAME HERE**
$username = '';		//**INSERT SERVER USERNAME HERE**
$password = '';			//**INSERT SERVER PASSWORD HERE**
$db = '';	//**INSERT DATABASE NAME HERE**

// Connect to database
$conn = new mysqli($server, $username, $password, $db) or die("Failed to connect to database.");

// Encode in UTF8 (Display Thai)
mysqli_set_charset($conn, "utf8");

// Declare query to get all data from database (Order by DESCending data = most recent first)
$sql = "SELECT * FROM media ORDER BY date DESC";

// Separated SQL query for line chart
$sql2 = "SELECT date, COUNT(case when `media_lv` = 1 then 1 else null end) as negative, COUNT(case when `media_lv` = 2 then 1 else null end) as neutral, COUNT(case when `media_lv` = 3 then 1 else null end) as positive FROM `media` GROUP BY date";

// Separated SQL query for bar chart
$sql3 = "SELECT media_type, COUNT(case when `media_lv` = 1 then 1 else null end) as negative, COUNT(case when `media_lv` = 2 then 1 else null end) as neutral, COUNT(case when `media_lv` = 3 then 1 else null end) as positive FROM `media` GROUP BY media_type";

// If the search form is submitted, use this query to get search result instead
if (isset($_POST['search'])) {
  // Select only headline or media name containing text in search field (Select ALL if blank)
  $sql = "SELECT * FROM media WHERE (haed OR media LIKE '%".$_POST['search']."%')";
  $sql2 = "SELECT date, COUNT(case when `media_lv` = 1 then 1 else null end) as negative, COUNT(case when `media_lv` = 2 then 1 else null end) as neutral, COUNT(case when `media_lv` = 3 then 1 else null end) as positive FROM `media` WHERE (haed OR media LIKE '%".$_POST['search']."%')";
  $sql3 = "SELECT media_type, COUNT(case when `media_lv` = 1 then 1 else null end) as negative, COUNT(case when `media_lv` = 2 then 1 else null end) as neutral, COUNT(case when `media_lv` = 3 then 1 else null end) as positive FROM `media` WHERE (haed OR media LIKE '%".$_POST['search']."%')";

  // AND select only data between two dates if both fields have input.
  if (($_POST['date1'] != "") && ($_POST['date2'] != "")) {
    $sql = $sql . " AND (date between '".$_POST['date1']."' and '".$_POST['date2']."')";
    $sql2 = $sql2 . " AND (date between '".$_POST['date1']."' and '".$_POST['date2']."')";
    $sql3 = $sql3 . " AND (date between '".$_POST['date1']."' and '".$_POST['date2']."')";
  }

  // AND select only specific media types if at least one media_type checkbox is checked 
  if (!empty($_POST['media'])) 
  {
    $sql = $sql . " AND (media_type IN (".implode(", ",$_POST['media'])."))";
    $sql2 = $sql2 . " AND (media_type IN (".implode(", ",$_POST['media'])."))";
    $sql3 = $sql3 . " AND (media_type IN (".implode(", ",$_POST['media'])."))";
  }

  // AND select only specific tones (positive/neutral/negative) 
  if (!empty($_POST['tone'])) 
  {
    $sql = $sql . " AND (media_lv IN (".implode(", ",$_POST['tone'])."))";
    $sql2 = $sql2 . " AND (media_lv IN (".implode(", ",$_POST['tone'])."))";
    $sql3 = $sql3 . " AND (media_lv IN (".implode(", ",$_POST['tone'])."))";
  }

  $sql = $sql . $_POST['sort'][0];
  $sql2 = $sql2 . " GROUP BY date";
  $sql3 = $sql3 . " GROUP BY media_type";
}

// Send query
$result = mysqli_query($conn, $sql) or die("$sql");
$result2 = mysqli_query($conn, $sql2) or die("$sql2");
$result3 = mysqli_query($conn, $sql3) or die("$sql3");

// #1 RESULT FOR SQL
// Declare data array (to store data counts)
$dataCount = 0;
$arrToneCount = array_fill(0, 4, 0);
$arrTypeCount = array_fill(0, 16, 0);

// Show data from database if there are more than 0 rows of data
if (mysqli_num_rows($result) > 0) {

  // Fetch each row from database
  while($row = mysqli_fetch_assoc($result)) {
    // Count data for pie charts
    $dataCount += 1;
    $arrToneCount[$row["media_lv"]] += 1;
    $arrTypeCount[$row["media_type"]] += 1;
  }

}

$searchResultText = "<div class='card'><b><i>Showing " . $dataCount . " result(s)</i></b>";

if (!empty($_POST['search'])) $searchResultText = $searchResultText . " for '" . $_POST['search'] . "'";
if (!empty($_POST['date1']) && !empty(['date2'])) $searchResultText = $searchResultText . " between " . $_POST['date1'] . " and " . $_POST['date2'];
if (!empty($_POST['media']) || !empty($_POST['tone'])) $searchResultText = $searchResultText . " with other filters applied";

echo $searchResultText . ":";

// #2 RESULT2 FOR SQL2
// For Line Chart: Get data from database
if (mysqli_num_rows($result2) > 0) {
  // Fetch each row from database
  while($row = mysqli_fetch_assoc($result2)) {
    // Array to store data and be converted to JSON (so that it can be used in Highcharts JS.)
    $arrDate[] = $row["date"];
    $arrNegCount[] = intval($row["negative"]);
    $arrNeuCount[] = intval($row["neutral"]);
    $arrPosCount[] = intval($row["positive"]);
  }
  $arrLineChartData = json_encode([$arrDate, $arrNegCount, $arrNeuCount, $arrPosCount]);
}

// #3 RESULT3 FOR SQL3
// For Bar Chart: Get data from database
if (mysqli_num_rows($result3) > 0) {
  // Fetch each row from database
  while($row = mysqli_fetch_assoc($result3)) {
    // Array to store data and be converted to JSON (so that it can be used in Highcharts JS.)
    switch($row["media_type"]) {
      case 1: $media_type_name = "Newspaper"; break;
      case 2: $media_type_name = "TV"; break;
      case 3: $media_type_name = "Radio"; break;
      case 4: $media_type_name = "Website"; break;
      case 5: $media_type_name = "Facebook"; break;
      case 6: $media_type_name = "Twitter"; break;
      case 7: $media_type_name = "Pantip"; break;
      case 8: $media_type_name = "YouTube"; break;
      case 9: $media_type_name = "Instagram"; break;
      default: $media_type_name = "unknown";
    }
    $arrType[] = $row["media_type"];
    $arrNegCountB[] = intval($row["negative"]);
    $arrNeuCountB[] = intval($row["neutral"]);
    $arrPosCountB[] = intval($row["positive"]);
    $arrTypeName[] = $media_type_name;
  }
  $arrBarChartData = json_encode([$arrType, $arrNegCountB, $arrNeuCountB, $arrPosCountB, $arrTypeName]);
}
?>


<script>
Highcharts.chart("pieChartTone", {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: "pie",
  },
  title: {
    text: "",
  },
  tooltip: {
    pointFormat: "Count: <b>{point.y}</b> <br>Percentage: <b>{point.percentage:.1f}%</b>",
  },
  accessibility: {
    point: {
      valueSuffix: "%",
    },
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: "pointer",
      dataLabels: {
        formatter: function() {
          if (this.y > 0) {
            return this.point.name + ': ' + Highcharts.numberFormat(this.point.percentage, 1) + ' %'
          }
        },
      },
    },
  },
  series: [
    {
      name: "Tone",
      colorByPoint: true,
      data: [
        {
          name: "Negative",
          y: <?php echo $arrToneCount[1]; ?>,
          color: '#ed7d31',
        },
        {
          name: "Neutral",
          y: <?php echo $arrToneCount[2]; ?>,
          color: '#5b9bd5',
        },
        {
          name: "Positive",
          y: <?php echo $arrToneCount[3]; ?>,
          color: '#70ad47',
        },
      ],
    },
  ],
});
</script>

<script>
Highcharts.chart("pieChartType", {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: "pie",
  },
  title: {
    text: "",
  },
  tooltip: {
    pointFormat: "Count: <b>{point.y}</b> <br>Percentage: <b>{point.percentage:.1f}%</b>",
  },
  accessibility: {
    point: {
      valueSuffix: "%",
    },
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: "pointer",
      dataLabels: {
        formatter: function() {
          if (this.y > 0) {
            return this.point.name + ': ' + Highcharts.numberFormat(this.point.percentage, 1) + ' %'
          }
        },
      },
    },
  },
  series: [
    {
      name: "Media Type",
      colorByPoint: true,
      data: [
        {
          name: "Newspaper",
          y: <?php echo $arrTypeCount[1]; ?>,
        },
        {
          name: "TV",
          y: <?php echo $arrTypeCount[2]; ?>,
        },
        {
          name: "Radio",
          y: <?php echo $arrTypeCount[3]; ?>,
        },
        {
          name: "Website",
          y: <?php echo $arrTypeCount[4]; ?>,
        },
        {
          name: "Facebook",
          y: <?php echo $arrTypeCount[5]; ?>,
        },
        {
          name: "Twitter",
          y: <?php echo $arrTypeCount[6]; ?>,
        },
        {
          name: "Pantip",
          y: <?php echo $arrTypeCount[7]; ?>,
        },
        {
          name: "Youtube",
          y: <?php echo $arrTypeCount[8]; ?>,
        },
        {
          name: "Instagram",
          y: <?php echo $arrTypeCount[9]; ?>,
        },
      ],
    },
  ],
});
</script>

<script>
Highcharts.chart('lineChartTone', {

  title: {
      text: ''
  },

  subtitle: {
      text: ''
  },

  yAxis: {
      title: {
          text: ''
      }
  },

  xAxis: {
    categories: <?php echo $arrLineChartData ?>[0],
  },

  legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'middle'
  },

  plotOptions: {
      series: {
          label: {
              connectorAllowed: false
          },
          pointStart: 0
      }
  },

  series: [{
        name: 'Negative',
        data: <?php echo $arrLineChartData ?>[1],
        color: '#ed7d31',
    }, {
        name: 'Neutral',
        data: <?php echo $arrLineChartData ?>[2],
        color: '#5b9bd5',
    }, {
        name: 'Positive',
        data: <?php echo $arrLineChartData ?>[3],
        color: '#70ad47',
    }],

  responsive: {
      rules: [{
          condition: {
              maxWidth: 500
          },
          chartOptions: {
              legend: {
                  layout: 'horizontal',
                  align: 'center',
                  verticalAlign: 'bottom'
              }
          }
      }]
  }

});
</script>

<script>
Highcharts.chart('barChartType', {
  chart: {
    type: 'bar'
  },
  title: {
    text: ''
  },
  subtitle: {
    text: ''
  },
  xAxis: {
    categories: <?php echo $arrBarChartData ?>[4],
    title: {
      text: null
    }
  },
  yAxis: {
    min: 0,
    title: {
      text: '',
      align: 'high'
    },
    labels: {
      overflow: 'justify'
    }
  },
  tooltip: {
    valueSuffix: ''
  },
  plotOptions: {
    bar: {
      dataLabels: {
        enabled: true
      }
    }
  },
  legend: {
    layout: 'vertical',
    align: 'right',
    verticalAlign: 'top',
    x: -40,
    y: 80,
    floating: true,
    borderWidth: 1,
    backgroundColor:
      Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
    shadow: true
  },
  credits: {
    enabled: false
  },
  series: [{
    name: 'Negative',
    data: <?php echo $arrBarChartData ?>[1],
    color: '#ed7d31',
  }, {
    name: 'Neutral',
    data: <?php echo $arrBarChartData ?>[2],
    color: '#5b9bd5',
  }, {
    name: 'Positive',
    data: <?php echo $arrBarChartData ?>[3],
    color: '#70ad47',
  }]
});
</script>


<?php
// Send query
$result = mysqli_query($conn, $sql) or die("$sql");

// Display output from database if there are more than 0 rows of data
if (mysqli_num_rows($result) > 0) {
  // Display output in HTML table
  //echo "<table><tr><th>Header</th><th>Media type</th><th>Date</th><th>Media Source</th></tr>";
  echo "<table id='resultTable'><tr><th>lv</th><th>Header</th><th style='min-width:100px;'>Media type</th><th style='min-width:100px;'>Date</th><th>Media Source</th></tr>";

  // Fetch each row from database
  while($row = mysqli_fetch_assoc($result)) {
    // Append each row to data
    $data[] = $row;
    // Match media_type with media_type_name
    switch($row["media_type"]) {
      case 1: $media_type_name = "Newspaper"; break;
      case 2: $media_type_name = "TV"; break;
      case 3: $media_type_name = "Radio"; break;
      case 4: $media_type_name = "Website"; break;
      case 5: $media_type_name = "Facebook"; break;
      case 6: $media_type_name = "Twitter"; break;
      case 7: $media_type_name = "Pantip"; break;
      case 8: $media_type_name = "YouTube"; break;
      case 9: $media_type_name = "Instagram"; break;
      default: $media_type_name = "unknown";
    }
    echo "<tr><td>" . $row["media_lv"] . "</td><td>" . $row["haed"] . "</td><td>" . $row["media_type"] . ". " . $media_type_name . "</td><td>" . $row["date"] . "</td><td>" . $row["media"] . "</td></tr>";
  }
  echo "</table></div>";

} else {
  echo "No results";
}
?>