<?php

/*

  ----------------------------------------------------------------------
GLPI - Gestionnaire libre de parc informatique
 Copyright (C) 2002 by the INDEPNET Development Team.
 Bazile Lebeau, baaz@indepnet.net - Jean-Mathieu Dol�ans, jmd@indepnet.net
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------
 LICENSE

This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

    Based on :   The example witch come with :
    Spreadsheet::WriteExcel written by John McNamara, jmcnamara@cpan.org
 ----------------------------------------------------------------------
 Original Author of file:
 Purpose of file:
 ----------------------------------------------------------------------


*/


include ("_relpos.php");

include ($phproot . "/glpi/includes.php");
include ($phproot . "/glpi/includes_networking.php");

checkAuthentication("normal");

$db = new DB;
//$query = "select * from glpi_networking";
$query = "select glpi_networking.*, glpi_networking_ports.ifaddr, glpi_networking_ports.ifmac ";
$query.= "from glpi_networking LEFT JOIN glpi_networking_ports ON (glpi_networking_ports.device_type = 2 AND glpi_networking_ports.on_device = glpi_networking.ID) ORDER by glpi_networking.ID";

$result = $db->query($query);
$num_field= $db->num_fields($result);
//set_time_limit(10);

require_once "class.writeexcel_workbook.inc.php";
require_once "class.writeexcel_worksheet.inc.php";

$fname = tempnam("tmp/", "reseaux.xls");
$workbook = &new writeexcel_workbook($fname);
$worksheet = &$workbook->addworksheet();

# Set the column width for columns 2 and 3
$worksheet->set_column(1, 2, 20);

# Set the row height for row 2
$worksheet->set_row(2, 30);

# Create a border format
$border1 =& $workbook->addformat();
$border1->set_color('white');
$border1->set_bold();
$border1->set_size(15);
$border1->set_pattern(0x1);
$border1->set_fg_color('green');
$border1->set_border_color('yellow');
$border1->set_top(6);
$border1->set_bottom(6);
$border1->set_left(6);
$border1->set_align('center');
$border1->set_align('vcenter');
$border1->set_merge(); # This is the key feature
/*
# Create another border format. Note you could use copy() here.
$border2 =& $workbook->addformat();
$border2->set_color('white');
$border2->set_bold();
$border2->set_size(15);
$border2->set_pattern(0x1);
$border2->set_fg_color('green');
$border2->set_border_color('yellow');
$border2->set_top(6);
$border2->set_bottom(6);
$border2->set_right(6);
$border2->set_align('center');
$border2->set_align('vcenter');
$border2->set_merge(); # This is the key feature
*/

# Only one cell should contain text, the others should be blank.
$worksheet->write(0, 0, $lang["networking"][42], $border1);
$worksheet->write(0, 1, $lang["networking"][0], $border1);
$worksheet->write(0, 2, $lang["networking"][5],  $border1);
$worksheet->write(0, 3, $lang["networking"][6], $border1);
$worksheet->write(0, 4, $lang["networking"][7], $border1);
$worksheet->write(0, 5, $lang["networking"][3], $border1);
$worksheet->write(0, 6, $lang["networking"][4], $border1);
$worksheet->write(0, 7, $lang["networking"][9], $border1);
$worksheet->write(0, 8, $lang["networking"][8], $border1);
$worksheet->write(0, 9, $lang["networking"][39], $border1);
$worksheet->write(0, 10, $lang["networking"][40], $border1);
$worksheet->write(0, 11, $lang["networking"][41], $border1);
$worksheet->write(0, 12, $lang["networking"][1],  $border1);
$worksheet->write(0, 13, $lang["networking"][2],   $border1);
$worksheet->write(0, 14, $lang["networking"][14], $border1);
$worksheet->write(0, 15, $lang["networking"][15], $border1);


$y=1;
$old_ID=-1;
$nb_skip=0; // Skip multiple interface computers
$table=array();
while($ligne = $db->fetch_array($result))
{
	// New computer
	if ($old_ID!=$ligne[0]){
		// reinit data
		for($i=0;$i<$num_field;$i++) {
					$name=$db->field_name($result,$i);
			if($name == "firmware") {
				$table[$i]=getDropdownName("glpi_dropdown_firmware",$ligne[$i]);
			}
			elseif($name == "location") {
				$table[$i]=getDropdownName("glpi_dropdown_locations",$ligne[$i]);
			}
			elseif($name == "type") {
				$table[$i]=getDropdownName("glpi_type_networking",$ligne[$i]);
			}
			else $table[$i]=$ligne[$i];
	}		
		$old_ID=$ligne[0];
	} 
	else { // Same computer
		$nb_skip++;
		// Add the new interface :
		for($i=$num_field-2;$i<$num_field;$i++)
		if ($ligne[$i]!="") $table[$i].="\n".$ligne[$i];
	}
	$worksheet->write_row($y-$nb_skip, 0, $table);
	
	$y++;
}

$workbook->close();

header("Content-Type: application/vnd.ms-excel");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>
