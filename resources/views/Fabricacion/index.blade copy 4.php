Index.php:

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

<!--    Datatables  -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.20/datatables.min.css"/>  
    <title></title>

  <style>
      table.dataTable thead {
            background: linear-gradient(to right, #fcb045, #fd1d1d, #833ab4);
            color:white;
        }
  </style>
  </head>
  <body>
    <h2 class="text-center">Datatables</h2>  
    <h3 class="text-center">Procesamiento ServerSide</h3>
    <div class="container">
       <div class="row">
           <div class="col-lg-12">
            <table id="tablaUsuarios" class="table table-striped table-bordered table-condensed" style="width:100%">
                <thead class="text-center">
                <tr>
                    <th>User_Id</th>
                    <th>userName</th>
                    <th>FirstName</th>                                
                    <th>LastName</th>  
                    <th>Gender</th>
                    <th>Password</th>
                    <th>Status</th>
                </tr>
                </thead>
            </table>   
           </div>
       </div> 
    </div>
   
    

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
      
      
<!--    Datatables-->
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.20/datatables.min.js"></script>  
      
    <script>
        $(document).ready(function(){
           $("#tablaUsuarios").DataTable({
              "processing": true,
              "serverSide": true,
              "sAjaxSource": "ServerSide/serversideUsuarios.php",
              "columnDefs":[{
                  "data":null
              }]   
           }); 
        });
    </script>  
      
  </body>
</html>


serverside.php: 

<?php
include 'serversideConexion.php';
class TableData { 
 	private $_db;
	public function __construct() {
		try {			
			$host		= HOST_SS;
			$database	= DATABASE_SS;
			$user		= USER_SS;
			$passwd		= PASSWORD_SS;
			
		    $this->_db = new PDO('mysql:host='.$host.';dbname='.$database, $user, $passwd, array(
				PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		} catch (PDOException $e) {
		    error_log("Failed to connect to database: ".$e->getMessage());
		}				
	}	
	public function get($table, $index_column, $columns) {
		// Paging
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		
		// Ordering
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) ) {
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
					$sortDir = (strcasecmp($_GET['sSortDir_'.$i], 'ASC') == 0) ? 'ASC' : 'DESC';
					$sOrder .= "`".$columns[ intval( $_GET['iSortCol_'.$i] ) ]."` ". $sortDir .", ";
				}
			}
			
			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" ) {
				$sOrder = "";
			}
		}
		
		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		$sWhere = "";
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($columns) ; $i++ ) {
				if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" ) {
					$sWhere .= "`".$columns[$i]."` LIKE :search OR ";
				}
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		
		// Individual column filtering
		for ( $i=0 ; $i<count($columns) ; $i++ ) {
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
				if ( $sWhere == "" ) {
					$sWhere = "WHERE ";
				}
				else {
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$columns[$i]."` LIKE :search".$i." ";
			}
		}
		
		// SQL queries get data to display
		$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $columns))."` FROM `".$table."` ".$sWhere." ".$sOrder." ".$sLimit;
		$statement = $this->_db->prepare($sQuery);
		
		// Bind parameters
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
			$statement->bindValue(':search', '%'.$_GET['sSearch'].'%', PDO::PARAM_STR);
		}
		for ( $i=0 ; $i<count($columns) ; $i++ ) {
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
				$statement->bindValue(':search'.$i, '%'.$_GET['sSearch_'.$i].'%', PDO::PARAM_STR);
			}
		}

		$statement->execute();
		$rResult = $statement->fetchAll();
		
		$iFilteredTotal = current($this->_db->query('SELECT FOUND_ROWS()')->fetch());
		
		// Get total number of rows in table
		$sQuery = "SELECT COUNT(`".$index_column."`) FROM `".$table."`";
		$iTotal = current($this->_db->query($sQuery)->fetch());
		
		// Output
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
		
		// Return array of values
		foreach($rResult as $aRow) {
			$row = array();			
			for ( $i = 0; $i < count($columns); $i++ ) {
				if ( $columns[$i] == "version" ) {
					// Special output formatting for 'version' column
					$row[] = ($aRow[ $columns[$i] ]=="0") ? '-' : $aRow[ $columns[$i] ];
				}
				else if ( $columns[$i] != ' ' ) {
					$row[] = $aRow[ $columns[$i] ];
				}
			}
			$output['aaData'][] = $row;
		}
		
		echo json_encode( $output );
	}
}
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
// Create instance of TableData class
$table_data = new TableData();
?>


serversideConexion.php:

<?php
define("HOST_SS","localhost");
define("DATABASE_SS","usuarios_db");
define("USER_SS","root");
define("PASSWORD_SS","");
?>

serversideUsuarios.php:

<?php
require 'serverside.php';
$table_data->get('vista_usuarios','user_id',array('user_id', 'username','first_name','last_name','gender','password','status'));

?>

