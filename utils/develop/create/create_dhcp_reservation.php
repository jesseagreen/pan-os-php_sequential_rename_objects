<?php
/**
 * ISC License
 *
 * Copyright (c) 2014-2018, Palo Alto Networks Inc.
 * Copyright (c) 2019, Palo Alto Networks Inc.
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */


set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());
require_once dirname(__FILE__)."/../../../lib/pan_php_framework.php";
require_once dirname(__FILE__)."/../../../utils/lib/UTIL.php";

PH::print_stdout();
PH::print_stdout("***********************************************");
PH::print_stdout("*********** " . basename(__FILE__) . " UTILITY **************");
PH::print_stdout();


PH::print_stdout( "PAN-OS-PHP version: ".PH::frameworkVersion() );


$supportedArguments = Array();
$supportedArguments['in'] = Array('niceName' => 'in', 'shortHelp' => 'input file or api. ie: in=config.xml  or in=api://192.168.1.1 or in=api://0018CAEC3@panorama.company.com', 'argDesc' => '[filename]|[api://IP]|[api://serial@IP]');
$supportedArguments['out'] = Array('niceName' => 'out', 'shortHelp' => 'output file to save config after changes. Only required when input is a file. ie: out=save-config.xml', 'argDesc' => '[filename]');
$supportedArguments['location'] = Array('niceName' => 'location', 'shortHelp' => 'specify if you want to limit your query to a VSYS. By default location=vsys1 for PANOS. ie: location=any or location=vsys2,vsys1', 'argDesc' => '=sub1[,sub2]');
$supportedArguments['debugapi'] = Array('niceName' => 'DebugAPI', 'shortHelp' => 'prints API calls when they happen');
$supportedArguments['help'] = Array('niceName' => 'help', 'shortHelp' => 'this message');
$supportedArguments['loadpanoramapushedconfig'] = Array('niceName' => 'loadPanoramaPushedConfig', 'shortHelp' => 'load Panorama pushed config from the firewall to take in account panorama objects and rules' );
$supportedArguments['folder'] = Array('niceName' => 'folder', 'shortHelp' => 'specify the folder where the offline files should be saved');

$usageMsg = PH::boldText('USAGE: ')."php ".basename(__FILE__)." in=api:://[MGMT-IP] or in=INPUTFILE.xml out=OUTFILE.xml";

$util = new UTIL( "custom", $argv, $argc, __FILE__, $supportedArguments, $usageMsg );
$util->utilInit();

##########################################
##########################################

$util->load_config();
$util->location_filter();

$pan = $util->pan;

##############

$sub = $pan->findVirtualSystem($util->objectsLocation[0]);

$zone_array = array();


$interface_name = "ethernet1/1";
$dhcp_array = array();
$dhcp_array[$interface_name] = array();

$tmp['ip'] = "192.168.1.1";
$tmp['mac'] = "01:02:03:04:05:06";
$dhcp_array[$interface_name][] = $tmp;

$tmp['ip'] = "192.168.1.20";
$tmp['mac'] = "01:02:03:04:05:08";
$dhcp_array[$interface_name][] = $tmp;

foreach( $dhcp_array as $interface_name => $tmp_array )
{
    $interface = $pan->network->findInterface($interface_name);
    if( $interface !== null )
    {
        $xpath = "/config/devices/entry[@name='localhost.localdomain']/network/dhcp/interface/entry[@name='".$interface->name()."']/server/reserved";

        $element = "";
        foreach( $tmp_array as $entry)
        {
            $ip = $entry['ip'];
            $mac = $entry['mac'];

            $element .= "<entry name='".$ip."'><mac>".$mac."</mac></entry>";
        }

        $con = $pan->connector;
        $con->sendSetRequest($xpath, $element);
    }
    else
    {
        mwarning( "Interface: ".$interface_name." not available; dhcp lease can not be added", null, false );
    }
}

##############################################

print "\n\n\n";

// save our work !!!
if( $util->configOutput !== null )
{
    if( $util->configOutput != '/dev/null' )
    {
        $pan->save_to_file($util->configOutput);
    }
}



print "\n\n************ END OF CREATE-INTERFACE UTILITY ************\n";
print     "**************************************************\n";
print "\n\n";
