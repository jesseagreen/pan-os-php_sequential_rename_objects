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

/**
 * Your journey will start from PANConf or PanoramaConf
 *
 * Code:
 *
 *  $pan = new PanoramaConf();
 *
 *  $pan->load_from_file('config.txt');
 *
 *  $pan->display_statistics();
 *
 * And there you go !
 *
 */
class PanoramaConf
{
    use PathableName;
    use PanSubHelperTrait;


    /** @var DOMElement */
    public $xmlroot;

    /** @var DOMDocument */
    public $xmldoc;

    /** @var DOMElement */
    public $sharedroot;
    public $devicesroot;
    public $localhostlocaldomain;

    public $deviceconfigroot;

    /** @var DOMElement */
    public $localhostroot;

    /** @var string[]|DomNode */
    public $templateroot;

    /** @var string[]|DomNode */
    public $templatestackroot;

    /** @var string[]|DomNode */
    public $devicegrouproot;

    /** @var string[]|DomNode */
    public $logcollectorgrouproot;


    public $version = null;

    public $timezone = null;

    public $managedFirewallsSerials = array();
    public $managedFirewallsStore;
    public $managedFirewallsSerialsModel = array();

    /** @var DeviceGroup[] */
    public $deviceGroups = array();

    /** @var Template[] */
    public $templates = array();

    /** @var TemplateStack[] */
    public $templatestacks = array();

    /** @var LogCollectorGroup[] */
    public $logCollectorGroups = array();

    /** @var RuleStore */
    public $securityRules;

    /** @var RuleStore */
    public $natRules;

    /** @var RuleStore */
    public $decryptionRules = null;

    /** @var RuleStore */
    public $appOverrideRules;

    /** @var RuleStore */
    public $captivePortalRules;

    /** @var RuleStore */
    public $authenticationRules;

    /** @var RuleStore */
    public $pbfRules;

    /** @var RuleStore */
    public $qosRules;

    /** @var RuleStore */
    public $dosRules;

    /** @var RuleStore */
    public $tunnelInspectionRules;

    /** @var RuleStore */
    public $defaultSecurityRules;

    /** @var RuleStore */
    public $networkPacketBrokerRules;

    /** @var RuleStore */
    public $sdWanRules;


    /** @var AddressStore */
    public $addressStore = null;

    /** @var ServiceStore */
    public $serviceStore = null;

    protected $securityProfilebaseroot;

    /** @var SecurityProfileStore */
    public $URLProfileStore = null;

    /** @var SecurityProfileStore */
    public $customURLProfileStore = null;

    /** @var SecurityProfileStore */
    public $AntiVirusProfileStore = null;

    /** @var SecurityProfileStore */
    public $VulnerabilityProfileStore = null;

    /** @var SecurityProfileStore */
    public $AntiSpywareProfileStore = null;

    /** @var SecurityProfileStore */
    public $FileBlockingProfileStore = null;

    /** @var SecurityProfileStore */
    public $DataFilteringProfileStore = null;

    /** @var SecurityProfileStore */
    public $WildfireProfileStore = null;


    /** @var SecurityProfileGroupStore */
    public $securityProfileGroupStore = null;


    /** @var SecurityProfileStore */
    public $DecryptionProfileStore = null;

    /** @var SecurityProfileStore */
    public $HipObjectsProfileStore = null;

    /** @var SecurityProfileStore */
    public $HipProfilesProfileStore = null;

    /** @var SecurityProfileStore */
    public $GTPProfileStore = null;

    /** @var SecurityProfileStore */
    public $SCEPProfileStore = null;

    /** @var SecurityProfileStore */
    public $PacketBrokerProfileStore = null;

    /** @var SecurityProfileStore */
    public $SDWanErrorCorrectionProfileStore = null;

    /** @var SecurityProfileStore */
    public $SDWanPathQualityProfileStore = null;

    /** @var SecurityProfileStore */
    public $SDWanSaasQualityProfileStore = null;

    /** @var SecurityProfileStore */
    public $SDWanTrafficDistributionProfileStore = null;

    /** @var SecurityProfileStore */
    public $DataObjectsProfileStore = null;


    /** @var ScheduleStore */
    public $scheduleStore = null;

    /** @var ZoneStore */
    public $zoneStore = null;

    /** @var CertificateStore */
    public $certificateStore = null;

    /** @var PANConf[] */
    public $managedFirewalls = array();


    /** @var PanAPIConnector|null $connector */
    public $connector = null;

    /** @var AppStore */
    public $appStore;

    /** @var ThreatStore */
    public $threatStore;

    /** @var SecurityProfileStore */
    public $urlStore;

    /** @var TagStore */
    public $tagStore;

    public $_fakeMode = FALSE;

    /** @var NetworkPropertiesContainer */
    public $_fakeNetworkProperties;

    public $name = '';


    public $_public_cloud_server = null;
    public $_auditComment = false;


    public function name()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->tagStore = new TagStore($this);
        $this->tagStore->setName('tagStore');

        $this->zoneStore = new ZoneStore($this);
        $this->zoneStore->setName('zoneStore');

        $this->certificateStore = new CertificateStore($this);
        $this->certificateStore->setName('certificateStore');

        $this->appStore = AppStore::getPredefinedStore( $this );

        $this->threatStore = ThreatStore::getPredefinedStore( $this );

        $this->urlStore = SecurityProfileStore::getPredefinedStore();

        $this->serviceStore = new ServiceStore($this);
        $this->serviceStore->name = 'services';

        $this->addressStore = new AddressStore($this);
        $this->addressStore->name = 'addresses';


        $this->customURLProfileStore = new SecurityProfileStore($this, "customURLProfile");
        $this->customURLProfileStore->name = 'CustomURL';

        $this->URLProfileStore = new SecurityProfileStore($this, "URLProfile");
        $this->URLProfileStore->name = 'URL';

        $this->AntiVirusProfileStore = new SecurityProfileStore($this, "AntiVirusProfile");
        $this->AntiVirusProfileStore->name = 'AntiVirus';


        $this->VulnerabilityProfileStore = new SecurityProfileStore($this, "VulnerabilityProfile");
        $this->VulnerabilityProfileStore->name = 'Vulnerability';

        $this->AntiSpywareProfileStore = new SecurityProfileStore($this, "AntiSpywareProfile");
        $this->AntiSpywareProfileStore->name = 'AntiSpyware';

        $this->FileBlockingProfileStore = new SecurityProfileStore($this, "FileBlockingProfile");
        $this->FileBlockingProfileStore->name = 'FileBlocking';

        $this->DataFilteringProfileStore = new SecurityProfileStore($this, "DataFilteringProfile");
        $this->DataFilteringProfileStore->name = 'DataFiltering';

        $this->WildfireProfileStore = new SecurityProfileStore($this, "WildfireProfile");
        $this->WildfireProfileStore->name = 'WildFire';


        $this->securityProfileGroupStore = new SecurityProfileGroupStore($this);
        $this->securityProfileGroupStore->name = 'SecurityProfileGroups';


        $this->DecryptionProfileStore = new SecurityProfileStore($this, "DecryptionProfile");
        $this->DecryptionProfileStore->name = 'Decryption';

        $this->HipObjectsProfileStore = new SecurityProfileStore($this, "HipObjectsProfile");
        $this->HipObjectsProfileStore->name = 'HipObjects';

        $this->HipProfilesProfileStore = new SecurityProfileStore($this, "HipProfilesProfile");
        $this->HipProfilesProfileStore->name = 'HipProfiles';

        $this->GTPProfileStore = new SecurityProfileStore($this, "GTPProfile");
        $this->GTPProfileStore->name = 'GTPProfiles';

        $this->SCEPProfileStore = new SecurityProfileStore($this, "SCEPProfile");
        $this->SCEPProfileStore->name = 'SCEPProfiles';

        $this->PacketBrokerProfileStore = new SecurityProfileStore($this, "PacketBrokerProfile");
        $this->PacketBrokerProfileStore->name = 'PacketBrokerProfiles';

        $this->SDWanErrorCorrectionProfileStore = new SecurityProfileStore($this, "SDWanErrorCorrectionProfile");
        $this->SDWanErrorCorrectionProfileStore->name = 'SDWanErrorCorrectionProfiles';

        $this->SDWanPathQualityProfileStore = new SecurityProfileStore($this, "SDWanPathQualityProfile");
        $this->SDWanPathQualityProfileStore->name = 'SDWanPathQualityProfiles';

        $this->SDWanSaasQualityProfileStore = new SecurityProfileStore($this, "SDWanSaasQualityProfile");
        $this->SDWanSaasQualityProfileStore->name = 'SDWanSaasQualityProfiles';

        $this->SDWanTrafficDistributionProfileStore = new SecurityProfileStore($this, "SDWanTrafficDistributionProfile");
        $this->SDWanTrafficDistributionProfileStore->name = 'SDWanTrafficDistributionProfiles';

        $this->DataObjectsProfileStore = new SecurityProfileStore($this, "DataObjectsProfile");
        $this->DataObjectsProfileStore->name = 'DataObjectsProfileStoreProfiles';


        $this->scheduleStore = new ScheduleStore($this);
        $this->scheduleStore->setName('scheduleStore');

        $this->securityRules = new RuleStore($this, 'SecurityRule', TRUE);
        $this->natRules = new RuleStore($this, 'NatRule', TRUE);
        $this->decryptionRules = new RuleStore($this, 'DecryptionRule', TRUE);
        $this->appOverrideRules = new RuleStore($this, 'AppOverrideRule', TRUE);
        $this->captivePortalRules = new RuleStore($this, 'CaptivePortalRule', TRUE);
        $this->authenticationRules = new RuleStore($this, 'AuthenticationRule', TRUE);
        $this->pbfRules = new RuleStore($this, 'PbfRule', TRUE);
        $this->qosRules = new RuleStore($this, 'QoSRule', TRUE);
        $this->dosRules = new RuleStore($this, 'DoSRule', TRUE);
        $this->tunnelInspectionRules = new RuleStore($this, 'TunnelInspectionRule', TRUE);
        $this->defaultSecurityRules = new RuleStore($this, 'DefaultSecurityRule', TRUE);

        $this->networkPacketBrokerRules = new RuleStore($this, 'NetworkPacketBrokerRule', TRUE);
        $this->sdWanRules = new RuleStore($this, 'SDWanRule', TRUE);

        $this->_fakeNetworkProperties = new NetworkPropertiesContainer($this);

        $this->dosRules->_networkStore = $this->_fakeNetworkProperties;
        $this->pbfRules->_networkStore = $this->_fakeNetworkProperties;

        #$this->managedFirewallsStore = new ManagedDeviceStore($this, 'managedFirewall', TRUE);
        $this->managedFirewallsStore = new ManagedDeviceStore( $this );
    }


    public function load_from_xmlstring(&$xml)
    {
        $this->xmldoc = new DOMDocument();

        if( $this->xmldoc->loadXML($xml, XML_PARSE_BIG_LINES) !== TRUE )
            derr('Invalid XML file found');

        $this->load_from_domxml($this->xmldoc);
    }

    /**
     * @param DOMElement|DOMDocument $xml
     * @throws Exception
     */
    public function load_from_domxml($xml, $debugLoadTime = false)
    {
        if( $xml->nodeType == XML_DOCUMENT_NODE )
        {
            $this->xmldoc = $xml;
            $this->xmlroot = DH::findFirstElementOrDie('config', $this->xmldoc);
        }
        else
        {
            $this->xmldoc = $xml->ownerDocument;
            $this->xmlroot = $xml;
        }

        $versionAttr = DH::findAttribute('version', $this->xmlroot);
        if( $versionAttr !== FALSE )
        {
            $this->version = PH::versionFromString($versionAttr);
        }
        else
        {
            if( isset($this->connector) && $this->connector !== null )
                $version = $this->connector->getSoftwareVersion();
            else
                derr('cannot find PANOS version used for make this config');

            $this->version = $version['version'];
        }


        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("mgt-config");

        $tmp = DH::findFirstElementOrCreate('mgt-config', $this->xmlroot);
        $tmp = DH::findFirstElement('devices', $tmp);
        if( $tmp !== false )
            $this->managedFirewallsStore->load_from_domxml($tmp);
        #$this->managedFirewallsSerials = $this->managedFirewallsStore->get_serial_from_xml($tmp, TRUE);


        if( is_object($this->connector) )
        {
            $this->managedFirewallsSerialsModel = $this->connector->panorama_getConnectedFirewallsSerials();
            foreach( $this->managedFirewallsSerialsModel as $serial => $fw )
            {
                $managedFirewall = $this->managedFirewallsStore->find($serial);
                $managedFirewall->isConnected = true;

                $managedFirewall->mgmtIP = $fw[ 'ip-address' ];
                $managedFirewall->version = $fw[ 'sw-version' ];
                $managedFirewall->model = $fw[ 'model' ];
                $managedFirewall->hostname = $fw[ 'hostname' ];
            }
        }


        $this->sharedroot = DH::findFirstElementOrCreate('shared', $this->xmlroot);

        $this->devicesroot = DH::findFirstElementOrDie('devices', $this->xmlroot);

        $this->localhostroot = DH::findFirstElementByNameAttrOrDie('entry', 'localhost.localdomain', $this->devicesroot);
        /*
        $this->localhostroot = DH::findFirstElement('entry', $this->devicesroot);
        if( $this->localhostroot === false )
        {
            $this->localhostroot = DH::createElement($this->devicesroot, 'entry');
            $this->localhostroot->setAttribute('name', 'localhost.localdomain');
        }
        */

        $this->deviceconfigroot = DH::findFirstElement('deviceconfig', $this->localhostroot);

        $this->devicegrouproot = DH::findFirstElementOrCreate('device-group', $this->localhostroot);
        $this->templateroot = DH::findFirstElementOrCreate('template', $this->localhostroot);
        $this->templatestackroot = DH::findFirstElementOrCreate('template-stack', $this->localhostroot);
        $this->logcollectorgrouproot = DH::findFirstElement('log-collector-group', $this->localhostroot);


        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("shared objects");
        //
        // Extract Tag objects
        //
        if( $this->version >= 60 )
        {
            $tmp = DH::findFirstElement('tag', $this->sharedroot);
            if( $tmp !== FALSE )
                $this->tagStore->load_from_domxml($tmp);
        }
        // End of Tag objects extraction


        //
        // Extract region objects
        //
        $tmp = DH::findFirstElement('region', $this->sharedroot);
        if( $tmp !== false )
            $this->addressStore->load_regions_from_domxml($tmp);
        // End of region objects extraction

        //
        // Shared address objects extraction
        //
        $tmp = DH::findFirstElement('address', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->addressStore->load_addresses_from_domxml($tmp);
        // end of address extraction

        //
        // Extract address groups
        //
        $tmp = DH::findFirstElement('address-group', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->addressStore->load_addressgroups_from_domxml($tmp);
        // End of address groups extraction


        //
        // Extract services
        //
        $tmp = DH::findFirstElement('service', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->serviceStore->load_services_from_domxml($tmp);
        // End of address groups extraction

        //
        // Extract service groups
        //
        $tmp = DH::findFirstElement('service-group', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->serviceStore->load_servicegroups_from_domxml($tmp);
        // End of address groups extraction

        //
        // Extract application
        //
        $tmp = DH::findFirstElement('application', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->appStore->load_application_custom_from_domxml($tmp);
        // End of application extraction

        //
        // Extract application filter
        //
        $tmp = DH::findFirstElement('application-filter', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->appStore->load_application_filter_from_domxml($tmp);
        // End of application filter groups extraction

        //
        // Extract application groups
        //
        $tmp = DH::findFirstElement('application-group', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->appStore->load_application_group_from_domxml($tmp);
        // End of application groups extraction


        // Extract SecurityProfiles objects
        //
        $this->securityProfilebaseroot = DH::findFirstElement('profiles', $this->sharedroot);
        if( $this->securityProfilebaseroot === FALSE )
            $this->securityProfilebaseroot = null;

        if( $this->securityProfilebaseroot !== null )
        {
            //
            // custom URL category extraction
            //
            $tmproot = DH::findFirstElement('custom-url-category', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->customURLProfileStore->load_from_domxml($tmproot);
                #$this->urlStore->load_from_domxml($tmproot);
            }

            //
            // URL Profile extraction
            //
            $tmproot = DH::findFirstElement('url-filtering', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                #$tmprulesroot = DH::findFirstElement('rules', $tmproot);
                #if( $tmprulesroot !== FALSE )
                $this->URLProfileStore->load_from_domxml($tmproot);
            }

            //
            // AntiVirus Profile extraction
            //
            $tmproot = DH::findFirstElement('virus', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                #$tmprulesroot = DH::findFirstElement('rules', $tmproot);
                #if( $tmprulesroot !== FALSE )
                $this->AntiVirusProfileStore->load_from_domxml($tmproot);
            }

            //
            // FileBlocking Profile extraction
            //
            $tmproot = DH::findFirstElement('file-blocking', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                #$tmprulesroot = DH::findFirstElement('rules', $tmproot);
                #if( $tmprulesroot !== FALSE )
                $this->FileBlockingProfileStore->load_from_domxml($tmproot);
            }

            //
            // DataFiltering Profile extraction
            //
            $tmproot = DH::findFirstElement('data-filtering', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
                $this->DataFilteringProfileStore->load_from_domxml($tmproot);

            //
            // vulnerability Profile extraction
            //
            $tmproot = DH::findFirstElement('vulnerability', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                #$tmprulesroot = DH::findFirstElement('rules', $tmproot);
                #if( $tmprulesroot !== FALSE )
                $this->VulnerabilityProfileStore->load_from_domxml($tmproot);
            }

            //
            // spyware Profile extraction
            //
            $tmproot = DH::findFirstElement('spyware', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                #$tmprulesroot = DH::findFirstElement('rules', $tmproot);
                #if( $tmprulesroot !== FALSE )
                $this->AntiSpywareProfileStore->load_from_domxml($tmproot);
            }

            //
            // wildfire Profile extraction
            //
            $tmproot = DH::findFirstElement('wildfire-analysis', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                #$tmprulesroot = DH::findFirstElement('rules', $tmproot);
                #if( $tmprulesroot !== FALSE )
                $this->WildfireProfileStore->load_from_domxml($tmproot);
            }

            //
            // Decryption Profile extraction
            //
            $tmproot = DH::findFirstElement('decryption', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->DecryptionProfileStore->load_from_domxml($tmproot);
            }

            //
            // HipObjects Profile extraction
            //
            $tmproot = DH::findFirstElement('hip-objects', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->HipObjectsProfileStore->load_from_domxml($tmproot);
            }

            //
            // HipProfiles Profile extraction
            //
            $tmproot = DH::findFirstElement('hip-profiles', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->HipProfilesProfileStore->load_from_domxml($tmproot);
            }

            //
            // GTP Profile extraction
            //
            $tmproot = DH::findFirstElement('gtp', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->GTPProfileStore->load_from_domxml($tmproot);
            }

            //
            // SCEP Profile extraction
            //
            $tmproot = DH::findFirstElement('scep', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->SCEPProfileStore->load_from_domxml($tmproot);
            }

            //
            // PacketBroker Profile extraction
            //
            $tmproot = DH::findFirstElement('packet-broker', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->PacketBrokerProfileStore->load_from_domxml($tmproot);
            }

            //
            // SDWan Error Correction Profile extraction
            //
            $tmproot = DH::findFirstElement('sdwan-error-correction', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->SDWanErrorCorrectionProfileStore->load_from_domxml($tmproot);
            }

            //
            // SDWan Path Quality Profile extraction
            //
            $tmproot = DH::findFirstElement('sdwan-path-quality', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->SDWanPathQualityProfileStore->load_from_domxml($tmproot);
            }

            //
            // SDWan Saas Quality Profile extraction
            //
            $tmproot = DH::findFirstElement('sdwan-saas-quality', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->SDWanSaasQualityProfileStore->load_from_domxml($tmproot);
            }

            //
            // SDWan Traffic Distribution Profile extraction
            //
            $tmproot = DH::findFirstElement('sdwan-traffic-distribution', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->SDWanTrafficDistributionProfileStore->load_from_domxml($tmproot);
            }

            //
            // DataObjects Profile extraction
            //
            $tmproot = DH::findFirstElement('data-objects', $this->securityProfilebaseroot);
            if( $tmproot !== FALSE )
            {
                $this->DataObjectsProfileStore->load_from_domxml($tmproot);
            }
        }


        //
        // Extract SecurityProfile groups in this DV
        //
        $tmp = DH::findFirstElement('profile-group', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->securityProfileGroupStore->load_securityprofile_groups_from_domxml($tmp);
        // End of address groups extraction

        //
        // Extract schedule objects
        //
        $tmp = DH::findFirstElement('schedule', $this->sharedroot);
        if( $tmp !== FALSE )
            $this->scheduleStore->load_from_domxml($tmp);
        // End of address groups extraction

        //
        // Extracting policies
        //
        $prerulebase = DH::findFirstElement('pre-rulebase', $this->sharedroot);
        $postrulebase = DH::findFirstElement('post-rulebase', $this->sharedroot);

        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('security', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('security', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->securityRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('nat', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('nat', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->natRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('decryption', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('decryption', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->decryptionRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('application-override', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('application-override', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->appOverrideRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('captive-portal', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('captive-portal', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->captivePortalRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('authentication', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('authentication', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->authenticationRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('pbf', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('pbf', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->pbfRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('qos', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('qos', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->qosRules->load_from_domxml($tmp, $tmpPost);


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('dos', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('dos', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->dosRules->load_from_domxml($tmp, $tmpPost);//


        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement('tunnel-inspect', $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement('tunnel-inspect', $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->tunnelInspectionRules->load_from_domxml($tmp, $tmpPost);//


        //default-security-Rules are only available on POST
        if( $prerulebase === FALSE )
            $tmp = null;
        else
            $tmp = null;
        if( $postrulebase === FALSE )
        {
            #$tmpPost = null;
            $postrulebase = DH::findFirstElementOrCreate('post-rulebase', $this->sharedroot);
        }

        #else{
            $sub = new Sub();
            $sub->rulebaseroot = $postrulebase;
            $sub->defaultSecurityRules = $this->defaultSecurityRules;
            $tmpPost = $sub->load_defaultSecurityRule( );
        #}
        if( $tmpPost !== FALSE )
            $this->defaultSecurityRules->load_from_domxml($tmp, $tmpPost);

        //network-packet-broker
        $xmlTagName = "network-packet-broker";
        $var = "networkPacketBrokerRules";
        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement($xmlTagName, $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement($xmlTagName, $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->$var->load_from_domxml($tmp, $tmpPost);

        //network-packet-broker
        $xmlTagName = "sdwan";
        $var = "sdWanRules";
        if( $prerulebase === FALSE )
            $tmp = null;
        else
        {
            $tmp = DH::findFirstElement($xmlTagName, $prerulebase);
            if( $tmp !== FALSE )
                $tmp = DH::findFirstElement('rules', $tmp);

            if( $tmp === FALSE )
                $tmp = null;
        }
        if( $postrulebase === FALSE )
            $tmpPost = null;
        else
        {
            $tmpPost = DH::findFirstElement($xmlTagName, $postrulebase);
            if( $tmpPost !== FALSE )
                $tmpPost = DH::findFirstElement('rules', $tmpPost);

            if( $tmpPost === FALSE )
                $tmpPost = null;
        }
        $this->$var->load_from_domxml($tmp, $tmpPost);
        //
        // end of policies extraction
        //


        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("Template");
        //
        // loading templates
        //
        foreach( $this->templateroot->childNodes as $node )
        {
            if( $node->nodeType != XML_ELEMENT_NODE ) continue;

            $ldv = new Template('*tmp*', $this);
            $ldv->load_from_domxml($node);
            $this->templates[] = $ldv;
            #PH::print_stdout(  "Template '{$ldv->name()}' found" );
        }
        //
        // end of Templates
        //

        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("TemplateStack");
        //
        // loading templatestacks
        //
        foreach( $this->templatestackroot->childNodes as $node )
        {
            if( $node->nodeType != XML_ELEMENT_NODE ) continue;

            $ldv = new TemplateStack('*tmp*', $this);
            $ldv->load_from_domxml($node);
            $this->templatestacks[] = $ldv;
            //PH::print_stdout(  "TemplateStack '{$ldv->name()}' found" );

            //Todo: add templates to templatestack
        }
        //
        // end of Templates
        //

        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("DeviceGroup");
        //
        // loading Device Groups now
        //
        if( $this->version < 70 || $this->_fakeMode )
        {
            foreach( $this->devicegrouproot->childNodes as $node )
            {
                if( $node->nodeType != XML_ELEMENT_NODE ) continue;
                //$lvname = $node->nodeName;
                //PH::print_stdout(  "Device Group '$lvname' found" );

                $ldv = new DeviceGroup($this);
                $ldv->load_from_domxml($node, $debugLoadTime);
                $this->deviceGroups[] = $ldv;
            }
        }
        else
        {
            if( $this->version < 80 )
                $dgMetaDataNode = DH::findXPathSingleEntry('/config/readonly/dg-meta-data/dginfo', $this->xmlroot);
            else
                $dgMetaDataNode = DH::findXPathSingleEntry('/config/readonly/devices/entry/device-group', $this->xmlroot);

            $dgToParent = array();
            $parentToDG = array();

            if( $dgMetaDataNode !== false )
                foreach( $dgMetaDataNode->childNodes as $node )
                {
                    if( $node->nodeType != XML_ELEMENT_NODE )
                        continue;

                    $dgName = DH::findAttribute('name', $node);
                    if( $dgName === FALSE )
                        derr("DeviceGroup name attribute not found in dg-meta-data", $node);

                    $parentDG = DH::findFirstElement('parent-dg', $node);
                    if( $parentDG === FALSE )
                    {
                        $dgToParent[$dgName] = 'shared';
                        $parentToDG['shared'][] = $dgName;
                    }
                    else
                    {
                        $dgToParent[$dgName] = $parentDG->textContent;
                        $parentToDG[$parentDG->textContent][] = $dgName;
                    }
                }

            $dgLoadOrder = array('shared');


            while( count($parentToDG) > 0 )
            {
                $dgLoadOrderCount = count($dgLoadOrder);

                foreach( $dgLoadOrder as &$dgName )
                {
                    if( isset($parentToDG[$dgName]) )
                    {
                        foreach( $parentToDG[$dgName] as &$newDGName )
                        {
                            $dgLoadOrder[] = $newDGName;
                        }
                        unset($parentToDG[$dgName]);
                    }
                }

                if( count($dgLoadOrder) <= $dgLoadOrderCount )
                {
                    PH::print_stdout(  "Problems could be available with the following DeviceGroup(s)" );
                    #print_r($dgLoadOrder);
                    print_r($parentToDG);
                    foreach( $parentToDG as $key => $dgName )
                    {
                        PH::print_stdout( "there is no DeviceGroup name: ".$key." available");
                        $tmp = DH::findFirstElementByNameAttr( "entry", $dgName[0], $dgMetaDataNode );
                        derr('dg-meta-data seems to be corrupted, parent.child template cannot be calculated ', $tmp, FALSE);
                    }
                }
            }

            /*PH::print_stdout(  "DG loading order:" );
            foreach( $dgLoadOrder as &$dgName )
                PH::print_stdout(  " - {$dgName}");*/


            $deviceGroupNodes = array();

            foreach( $this->devicegrouproot->childNodes as $node )
            {
                if( $node->nodeType != XML_ELEMENT_NODE )
                    continue;

                $nodeNameAttr = DH::findAttribute('name', $node);
                if( $nodeNameAttr === FALSE )
                    derr("DeviceGroup 'name' attribute was not found", $node);

                if( !is_string($nodeNameAttr) || $nodeNameAttr == '' )
                    derr("DeviceGroup 'name' attribute has invalid value", $node);

                $deviceGroupNodes[$nodeNameAttr] = $node;
            }

            foreach( $dgLoadOrder as $dgIndex => &$dgName )
            {
                if( $dgName == 'shared' )
                    continue;

                if( !isset($deviceGroupNodes[$dgName]) )
                {
                    mwarning("DeviceGroup '$dgName' is listed in dg-meta-data but doesn't exist in XML");
                    //unset($dgLoadOrder[$dgIndex]);
                    continue;
                }

                $ldv = new DeviceGroup($this);
                if( !isset($dgToParent[$dgName]) )
                {
                    mwarning("DeviceGroup '$dgName' has not parent associated, assuming SHARED");
                }
                elseif( $dgToParent[$dgName] == 'shared' )
                {
                    // do nothing
                }
                else
                {
                    $parentDG = $this->findDeviceGroup($dgToParent[$dgName]);
                    if( $parentDG === null )
                        mwarning("DeviceGroup '$dgName' has DG '{$dgToParent[$dgName]}' listed as parent but it cannot be found in XML");
                    else
                    {
                        $parentDG->_childDeviceGroups[$dgName] = $ldv;
                        $ldv->parentDeviceGroup = $parentDG;

                        $storeType = array(
                            'addressStore', 'serviceStore', 'tagStore', 'scheduleStore', 'appStore',

                            'securityProfileGroupStore',

                            'URLProfileStore', 'AntiVirusProfileStore', 'FileBlockingProfileStore', 'DataFilteringProfileStore',
                            'VulnerabilityProfileStore', 'AntiSpywareProfileStore', 'WildfireProfileStore',
                            'DecryptionProfileStore', 'HipObjectsProfileStore'

                            );

                        foreach( $storeType as $type )
                            $ldv->$type->parentCentralStore = $parentDG->$type;
                    }
                }

                if( $debugLoadTime )
                    PH::print_DEBUG_loadtime("DeviceGroup - ".$dgName);

                $ldv->load_from_domxml($deviceGroupNodes[$dgName], $debugLoadTime);
                $this->deviceGroups[] = $ldv;

            }

        }
        //
        // End of DeviceGroup loading
        //

        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("LogCollectorGroup");
        //
        // loading LogCollectorGroup
        //
        if( $this->logcollectorgrouproot !== FALSE )
        {
            foreach( $this->logcollectorgrouproot->childNodes as $node )
            {
                if( $node->nodeType != XML_ELEMENT_NODE ) continue;

                #$ldv = new LogCollectorGroup('*tmp*', $this);
                $ldv = new LogCollectorGroup( $this);
                $ldv->load_from_domxml($node);
                $this->logCollectorGroups[] = $ldv;
                //PH::print_stdout(  "TemplateStack '{$ldv->name()}' found" );

                //Todo: add templates to templatestack
            }
        }
        //
        // end of LogCollectorGroup
        //


        if( $debugLoadTime )
            PH::print_DEBUG_loadtime("Device config");
        //
        // Extract setting related configs
        //
        if( $this->deviceconfigroot !== FALSE )
        {
            $settingroot = DH::findFirstElement('setting', $this->deviceconfigroot);
            if( $settingroot !== FALSE )
            {
                $tmp1 = DH::findFirstElement('wildfire', $settingroot);
                if( $tmp1 !== FALSE )
                {
                    $tmp2 = DH::findFirstElement('public-cloud-server', $tmp1);
                    if( $tmp2 )
                    {
                        $this->_public_cloud_server = $tmp1->textContent;
                    }
                }

                $managementroot = DH::findFirstElement('management', $settingroot);
                if( $managementroot !== FALSE )
                {
                    $auditComment = DH::findFirstElement('rule-require-audit-comment', $managementroot);
                    if( $auditComment != FALSE )
                        if( $auditComment->textContent === "yes" )
                            $this->_auditComment = TRUE;
                }
            }

            $systemroot = DH::findFirstElement('system', $this->deviceconfigroot);
            if( $systemroot !== FALSE )
            {
                $timezone = DH::findFirstElement('timezone', $systemroot);
                if( $timezone )
                {
                    $this->timezone = $timezone->textContent;
                    date_default_timezone_set( $timezone->textContent );
                }
            }
        }
        //
    }


    /**
     * @param string $name
     * @return DeviceGroup|null
     */
    public function findDeviceGroup($name)
    {
        if( $name == "shared" )
            return $this;

        foreach( $this->deviceGroups as $dg )
        {
            if( $dg->name() == $name )
                return $dg;
        }

        return null;
    }

    /**
     * @param string $name
     * @return Template|null
     */
    public function findTemplate($name)
    {
        foreach( $this->templates as $template )
        {
            if( $template->name() == $name )
                return $template;
        }

        return null;
    }

    /**
     * @param string $name
     * @return TemplateStack|null
     */
    public function findTemplateStack($name)
    {
        foreach( $this->templatestacks as $templatestack )
        {
            if( $templatestack->name() == $name )
                return $templatestack;
        }

        return null;
    }

    /**
     * @param string $fileName
     * @param bool $printMessage
     * @param int $indentingXml
     */
    public function save_to_file($fileName, $printMessage = TRUE, $lineReturn = TRUE, $indentingXml = 0, $indentingXmlIncreament = 1)
    {
        if( $printMessage )
            PH::print_stdout( "Now saving PANConf to file '$fileName'..." );

        //Todo: swaschkut check
        //$indentingXmlIncreament was 2 per default for Panroama
        $xml = &DH::dom_to_xml($this->xmlroot, $indentingXml, $lineReturn, -1, $indentingXmlIncreament + 1);

        $path_parts = pathinfo($fileName);
        if (!is_dir($path_parts['dirname']))
            mkdir($path_parts['dirname'], 0777, true);

        file_put_contents($fileName, $xml);

        if( $printMessage )
            PH::print_stdout( "     done!");
    }

    /**
     * @param string $fileName
     */
    public function load_from_file($fileName)
    {
        $filecontents = file_get_contents($fileName);

        $this->load_from_xmlstring($filecontents);

    }


    public function display_statistics( $connector = null )
    {

        $gpreSecRules = $this->securityRules->countPreRules();
        $gpreNatRules = $this->natRules->countPreRules();
        $gpreDecryptRules = $this->decryptionRules->countPreRules();
        $gpreAppOverrideRules = $this->appOverrideRules->countPreRules();
        $gpreCPRules = $this->captivePortalRules->countPreRules();
        $gpreAuthRules = $this->authenticationRules->countPreRules();
        $gprePbfRules = $this->pbfRules->countPreRules();
        $gpreQoSRules = $this->qosRules->countPreRules();
        $gpreDoSRules = $this->dosRules->countPreRules();

        $gpreTunnelInspectionRules = $this->tunnelInspectionRules->countPreRules();
        #$gpreDefaultSecurityRules = $this->defaultSecurityRules->countPreRules();
        $gpreNetworkPacketBrockerRules = $this->networkPacketBrokerRules->countPreRules();
        $gpreSDWanRules = $this->sdWanRules->countPreRules();


        $gpostSecRules = $this->securityRules->countPostRules();
        $gpostNatRules = $this->natRules->countPostRules();
        $gpostDecryptRules = $this->decryptionRules->countPostRules();
        $gpostAppOverrideRules = $this->appOverrideRules->countPostRules();
        $gpostCPRules = $this->captivePortalRules->countPostRules();
        $gpostAuthRules = $this->authenticationRules->countPostRules();
        $gpostPbfRules = $this->pbfRules->countPostRules();
        $gpostQoSRules = $this->qosRules->countPostRules();
        $gpostDoSRules = $this->dosRules->countPostRules();

        $gpostTunnelInspectionRules = $this->tunnelInspectionRules->countPostRules();
        $gpostDefaultSecurityRules = $this->defaultSecurityRules->countPostRules();
        $gpostNetworkPacketBrockerRules = $this->networkPacketBrokerRules->countPostRules();
        $gpostSDWanRules = $this->sdWanRules->countPostRules();


        $gnservices = $this->serviceStore->countServices();
        $gnservicesUnused = $this->serviceStore->countUnusedServices();
        $gnserviceGs = $this->serviceStore->countServiceGroups();
        $gnserviceGsUnused = $this->serviceStore->countUnusedServiceGroups();
        $gnTmpServices = $this->serviceStore->countTmpServices();

        $gnaddresss = $this->addressStore->countAddresses();
        $gnaddresssUnused = $this->addressStore->countUnusedAddresses();
        $gnaddressGs = $this->addressStore->countAddressGroups();
        $gnaddressGsUnused = $this->addressStore->countUnusedAddressGroups();
        $gnTmpAddresses = $this->addressStore->countTmpAddresses();
        $gnRegionAddresses = $this->addressStore->countRegionObjects();

        $gTagCount = $this->tagStore->count();
        $gTagUnusedCount = $this->tagStore->countUnused();

        $gnsecurityprofileGs = $this->securityProfileGroupStore->count();

        $gnantispyware = $this->AntiSpywareProfileStore->count();
        $gnvulnerability = $this->VulnerabilityProfileStore->count();
        $gnantivirus = $this->AntiVirusProfileStore->count();
        $gnwildfire = $this->WildfireProfileStore->count();
        $gnurlprofil = $this->URLProfileStore->count();
        $gncustomurlprofil = $this->customURLProfileStore->count();
        $gnfileblocking = $this->FileBlockingProfileStore->count();
        $gndecryption = $this->DecryptionProfileStore->count();

        $gnhipobjects = $this->HipObjectsProfileStore->count();
        $gnhipprofiles = $this->HipProfilesProfileStore->count();

        $gngtp = $this->GTPProfileStore->count();
        $gnscep = $this->SCEPProfileStore->count();
        $gnpacketbroker = $this->PacketBrokerProfileStore->count();

        $gnsdwanerrorcorrection = $this->SDWanErrorCorrectionProfileStore->count();
        $gnsdwanpathquality = $this->SDWanPathQualityProfileStore->count();
        $gnsdwansaasquality = $this->SDWanSaasQualityProfileStore->count();
        $gnsdwantrafficdistribution = $this->SDWanTrafficDistributionProfileStore->count();

        $gndataobjects = $this->DataObjectsProfileStore->count();

        foreach( $this->deviceGroups as $cur )
        {
            $gpreSecRules += $cur->securityRules->countPreRules();
            $gpreNatRules += $cur->natRules->countPreRules();
            $gpreDecryptRules += $cur->decryptionRules->countPreRules();
            $gpreAppOverrideRules += $cur->appOverrideRules->countPreRules();
            $gpreCPRules += $cur->captivePortalRules->countPreRules();
            $gpreAuthRules += $cur->authenticationRules->countPreRules();
            $gprePbfRules += $cur->pbfRules->countPreRules();
            $gpreQoSRules += $cur->qosRules->countPreRules();
            $gpreDoSRules += $cur->dosRules->countPreRules();

            $gpreTunnelInspectionRules += $cur->tunnelInspectionRules->countPreRules();
            #$gpreDefaultSecurityRules += $cur->defaultSecurityRules->countPreRules();
            $gpreNetworkPacketBrockerRules += $cur->networkPacketBrokerRules->countPreRules();
            $gpreSDWanRules += $cur->sdWanRules->countPreRules();


            $gpostSecRules += $cur->securityRules->countPostRules();
            $gpostNatRules += $cur->natRules->countPostRules();
            $gpostDecryptRules += $cur->decryptionRules->countPostRules();
            $gpostAppOverrideRules += $cur->appOverrideRules->countPostRules();
            $gpostCPRules += $cur->captivePortalRules->countPostRules();
            $gpostAuthRules += $cur->authenticationRules->countPostRules();
            $gpostPbfRules += $cur->pbfRules->countPostRules();
            $gpostQoSRules += $cur->qosRules->countPostRules();
            $gpostDoSRules += $cur->dosRules->countPostRules();

            $gpostTunnelInspectionRules += $cur->tunnelInspectionRules->countPostRules();
            $gpostDefaultSecurityRules += $cur->defaultSecurityRules->countPostRules();
            $gpostNetworkPacketBrockerRules += $cur->networkPacketBrokerRules->countPostRules();
            $gpostSDWanRules += $cur->sdWanRules->countPostRules();


            $gnservices += $cur->serviceStore->countServices();
            $gnservicesUnused += $cur->serviceStore->countUnusedServices();
            $gnserviceGs += $cur->serviceStore->countServiceGroups();
            $gnserviceGsUnused += $cur->serviceStore->countUnusedServiceGroups();
            $gnTmpServices += $cur->serviceStore->countTmpServices();

            $gnaddresss += $cur->addressStore->countAddresses();
            $gnaddresssUnused += $cur->addressStore->countUnusedAddresses();
            $gnaddressGs += $cur->addressStore->countAddressGroups();
            $gnaddressGsUnused += $cur->addressStore->countUnusedAddressGroups();
            $gnTmpAddresses += $cur->addressStore->countTmpAddresses();
            $gnRegionAddresses += $cur->addressStore->countRegionObjects();

            $gTagCount += $cur->tagStore->count();
            $gTagUnusedCount += $cur->tagStore->countUnused();


            $gnsecurityprofileGs += $cur->securityProfileGroupStore->count();

            $gnantispyware += $cur->AntiSpywareProfileStore->count();
            $gnvulnerability += $cur->VulnerabilityProfileStore->count();
            $gnantivirus += $cur->AntiVirusProfileStore->count();
            $gnwildfire += $cur->WildfireProfileStore->count();
            $gnurlprofil += $cur->URLProfileStore->count();
            $gncustomurlprofil += $cur->customURLProfileStore->count();
            $gnfileblocking += $cur->FileBlockingProfileStore->count();
            $gndecryption += $cur->DecryptionProfileStore->count();

            $gnhipobjects += $cur->HipObjectsProfileStore->count();
            $gnhipprofiles += $cur->HipProfilesProfileStore->count();

            $gngtp += $cur->GTPProfileStore->count();
            $gnscep += $cur->SCEPProfileStore->count();
            $gnpacketbroker += $cur->PacketBrokerProfileStore->count();

            $gnsdwanerrorcorrection += $cur->SDWanErrorCorrectionProfileStore->count();
            $gnsdwanpathquality += $cur->SDWanPathQualityProfileStore->count();
            $gnsdwansaasquality += $cur->SDWanSaasQualityProfileStore->count();
            $gnsdwantrafficdistribution += $cur->SDWanTrafficDistributionProfileStore->count();

            $gndataobjects += $cur->DataObjectsProfileStore->count();
        }

        $stdoutarray = array();

        $stdoutarray['type'] = get_class( $this );

        $header = "Statistics for PanoramaConf '" . $this->name . "'";
        $stdoutarray['header'] = $header;

        $stdoutarray['pre security rules'] = array();
        $stdoutarray['pre security rules']['shared'] = $this->securityRules->countPreRules();
        $stdoutarray['pre security rules']['total_DGs'] = $gpreSecRules;

        $stdoutarray['post security rules'] = array();
        $stdoutarray['post security rules']['shared'] = $this->securityRules->countPostRules();
        $stdoutarray['post security rules']['total_DGs'] = $gpostSecRules;


        $stdoutarray['pre nat rules'] = array();
        $stdoutarray['pre nat rules']['shared'] = $this->natRules->countPreRules();
        $stdoutarray['pre nat rules']['total_DGs'] = $gpreNatRules;

        $stdoutarray['post nat rules'] = array();
        $stdoutarray['post nat rules']['shared'] = $this->natRules->countPostRules();
        $stdoutarray['post nat rules']['total_DGs'] = $gpostNatRules;


        $stdoutarray['pre qos rules'] = array();
        $stdoutarray['pre qos rules']['shared'] = $this->qosRules->countPreRules();
        $stdoutarray['pre qos rules']['total_DGs'] = $gpreQoSRules;

        $stdoutarray['post qos rules'] = array();
        $stdoutarray['post qos rules']['shared'] = $this->qosRules->countPostRules();
        $stdoutarray['post qos rules']['total_DGs'] = $gpostQoSRules;


        $stdoutarray['pre pbf rules'] = array();
        $stdoutarray['pre pbf rules']['shared'] = $this->pbfRules->countPreRules();
        $stdoutarray['pre pbf rules']['total_DGs'] = $gprePbfRules;

        $stdoutarray['post pbf rules'] = array();
        $stdoutarray['post pbf rules']['shared'] = $this->pbfRules->countPostRules();
        $stdoutarray['post pbf rules']['total_DGs'] = $gpostPbfRules;


        $stdoutarray['pre decryption rules'] = array();
        $stdoutarray['pre decryption rules']['shared'] = $this->decryptionRules->countPreRules();
        $stdoutarray['pre decryption rules']['total_DGs'] = $gpreDecryptRules;

        $stdoutarray['post decryption rules'] = array();
        $stdoutarray['post decryption rules']['shared'] = $this->decryptionRules->countPostRules();
        $stdoutarray['post decryption rules']['total_DGs'] = $gpostDecryptRules;


        $stdoutarray['pre app-override rules'] = array();
        $stdoutarray['pre app-override rules']['shared'] = $this->appOverrideRules->countPreRules();
        $stdoutarray['pre app-override rules']['total_DGs'] = $gpreAppOverrideRules;

        $stdoutarray['post app-override rules'] = array();
        $stdoutarray['post app-override rules']['shared'] = $this->appOverrideRules->countPostRules();
        $stdoutarray['post app-override rules']['total_DGs'] = $gpostAppOverrideRules;


        $stdoutarray['pre capt-portal rules'] = array();
        $stdoutarray['pre capt-portal rules']['shared'] = $this->captivePortalRules->countPreRules();
        $stdoutarray['pre capt-portal rules']['total_DGs'] = $gpreCPRules;

        $stdoutarray['post capt-portal rules'] = array();
        $stdoutarray['post capt-portal rules']['shared'] = $this->captivePortalRules->countPostRules();
        $stdoutarray['post capt-portal rules']['total_DGs'] = $gpostCPRules;


        $stdoutarray['pre authentication rules'] = array();
        $stdoutarray['pre authentication rules']['shared'] = $this->authenticationRules->countPreRules();
        $stdoutarray['pre authentication rules']['total_DGs'] = $gpreAuthRules;

        $stdoutarray['post authentication rules'] = array();
        $stdoutarray['post authentication rules']['shared'] = $this->authenticationRules->countPostRules();
        $stdoutarray['post authentication rules']['total_DGs'] = $gpostAuthRules;


        $stdoutarray['pre dos rules'] = array();
        $stdoutarray['pre dos rules']['shared'] = $this->dosRules->countPreRules();
        $stdoutarray['pre dos rules']['total_DGs'] = $gpreDoSRules;

        $stdoutarray['post dos rules'] = array();
        $stdoutarray['post dos rules']['shared'] = $this->dosRules->countPostRules();
        $stdoutarray['post dos rules']['total_DGs'] = $gpostDoSRules;

        $stdoutarray['pre tunnel-inspection rules'] = array();
        $stdoutarray['pre tunnel-inspection rules']['shared'] = $this->tunnelInspectionRules->countPreRules();
        $stdoutarray['pre tunnel-inspection rules']['total_DGs'] = $gpreTunnelInspectionRules;

        $stdoutarray['post tunnel-inspection rules'] = array();
        $stdoutarray['post tunnel-inspection rules']['shared'] = $this->tunnelInspectionRules->countPostRules();
        $stdoutarray['post tunnel-inspection rules']['total_DGs'] = $gpostTunnelInspectionRules;

        #pre default-security not existent
        #$stdoutarray['pre default-security rules'] = array();
        #$stdoutarray['pre default-security rules']['shared'] = $this->defaultSecurityRules->countPreRules();
        #$stdoutarray['pre default-security rules']['total_DGs'] = $gpreDefaultSecurityRules;

        $stdoutarray['post default-security rules'] = array();
        $stdoutarray['post default-security rules']['shared'] = $this->defaultSecurityRules->countPostRules();
        $stdoutarray['post default-security rules']['total_DGs'] = $gpostDefaultSecurityRules;

        $stdoutarray['pre network-packet-broker rules'] = array();
        $stdoutarray['pre network-packet-broker rules']['shared'] = $this->networkPacketBrokerRules->countPreRules();
        $stdoutarray['pre network-packet-broker rules']['total_DGs'] = $gpreNetworkPacketBrockerRules;

        $stdoutarray['post network-packet-broker rules'] = array();
        $stdoutarray['post network-packet-broker rules']['shared'] = $this->networkPacketBrokerRules->countPostRules();
        $stdoutarray['post network-packet-broker rules']['total_DGs'] = $gpostNetworkPacketBrockerRules;

        $stdoutarray['pre sdwan rules'] = array();
        $stdoutarray['pre sdwan rules']['shared'] = $this->sdWanRules->countPreRules();
        $stdoutarray['pre sdwan rules']['total_DGs'] = $gpreSDWanRules;

        $stdoutarray['post sdwan rules'] = array();
        $stdoutarray['post sdwan rules']['shared'] = $this->sdWanRules->countPostRules();
        $stdoutarray['post sdwan rules']['total_DGs'] = $gpostSDWanRules;

        $stdoutarray['address objects'] = array();
        $stdoutarray['address objects']['shared'] = $this->addressStore->countAddresses();
        $stdoutarray['address objects']['total_DGs'] = $gnaddresss;
        $stdoutarray['address objects']['unused'] = $gnaddresssUnused;

        $stdoutarray['addressgroup objects'] = array();
        $stdoutarray['addressgroup objects']['shared'] = $this->addressStore->countAddressGroups();
        $stdoutarray['addressgroup objects']['total_DGs'] = $gnaddressGs;
        $stdoutarray['addressgroup objects']['unused'] = $gnaddressGsUnused;

        $stdoutarray['temporary address objects'] = array();
        $stdoutarray['temporary address objects']['shared'] = $this->addressStore->countTmpAddresses();
        $stdoutarray['temporary address objects']['total_DGs'] = $gnTmpAddresses;

        $stdoutarray['region objects'] = array();
        $stdoutarray['region objects']['shared'] = $this->addressStore->countRegionObjects();
        $stdoutarray['region objects']['total_DGs'] = $gnRegionAddresses;

        $stdoutarray['service objects'] = array();
        $stdoutarray['service objects']['shared'] = $this->serviceStore->countServices();
        $stdoutarray['service objects']['total_DGs'] = $gnservices;
        $stdoutarray['service objects']['unused'] = $gnservicesUnused;

        $stdoutarray['servicegroup objects'] = array();
        $stdoutarray['servicegroup objects']['shared'] = $this->serviceStore->countServiceGroups();
        $stdoutarray['servicegroup objects']['total_DGs'] = $gnserviceGs;
        $stdoutarray['servicegroup objects']['unused'] = $gnserviceGsUnused;

        $stdoutarray['temporary service objects'] = array();
        $stdoutarray['temporary service objects']['shared'] = $this->serviceStore->countTmpServices();
        $stdoutarray['temporary service objects']['total_DGs'] = $gnTmpServices;


        $stdoutarray['tag objects'] = array();
        $stdoutarray['tag objects']['shared'] = $this->tagStore->count();
        $stdoutarray['tag objects']['total_DGs'] = $gTagCount;
        $stdoutarray['tag objects']['unused'] = $gTagUnusedCount;

        $stdoutarray['securityProfileGroup objects'] = array();
        $stdoutarray['securityProfileGroup objects']['shared'] = $this->securityProfileGroupStore->count();
        $stdoutarray['securityProfileGroup objects']['total_DGs'] = $gnsecurityprofileGs;


        $stdoutarray['Anti-Spyware objects'] = array();
        $stdoutarray['Anti-Spyware objects']['shared'] = $this->AntiSpywareProfileStore->count();
        $stdoutarray['Anti-Spyware objects']['total_DGs'] = $gnantispyware;
        $stdoutarray['Vulnerability objects'] = array();
        $stdoutarray['Vulnerability objects']['shared'] = $this->VulnerabilityProfileStore->count();
        $stdoutarray['Vulnerability objects']['total_DGs'] = $gnvulnerability;
        $stdoutarray['Antivirus objects'] = array();
        $stdoutarray['Antivirus objects']['shared'] = $this->AntiVirusProfileStore->count();
        $stdoutarray['Antivirus objects']['total_DGs'] = $gnantivirus;
        $stdoutarray['Wildfire objects'] = array();
        $stdoutarray['Wildfire objects']['shared'] = $this->WildfireProfileStore->count();
        $stdoutarray['Wildfire objects']['total_DGs'] = $gnwildfire;
        $stdoutarray['URL objects'] = array();
        $stdoutarray['URL objects']['shared'] = $this->URLProfileStore->count();
        $stdoutarray['URL objects']['total_DGs'] = $gnurlprofil;
        $stdoutarray['custom URL objects'] = array();
        $stdoutarray['custom URL objects']['shared'] = $this->customURLProfileStore->count();
        $stdoutarray['custom URL objects']['total_DGs'] = $gncustomurlprofil;
        $stdoutarray['File-Blocking objects'] = array();
        $stdoutarray['File-Blocking objects']['shared'] = $this->FileBlockingProfileStore->count();
        $stdoutarray['File-Blocking objects']['total_DGs'] = $gnfileblocking;
        $stdoutarray['Decryption objects'] = array();
        $stdoutarray['Decryption objects']['shared'] = $this->DecryptionProfileStore->count();
        $stdoutarray['Decryption objects']['total_DGs'] = $gndecryption;

        $stdoutarray['HipObject objects'] = array();
        $stdoutarray['HipObject objects']['shared'] = $this->HipObjectsProfileStore->count();
        $stdoutarray['HipObject objects']['total_DGs'] = $gnhipobjects;
        $stdoutarray['HipProfile objects'] = array();
        $stdoutarray['HipProfile objects']['shared'] = $this->HipProfilesProfileStore->count();
        $stdoutarray['HipProfile objects']['total_DGs'] = $gnhipprofiles;

        $stdoutarray['GTP objects'] = array();
        $stdoutarray['GTP objects']['shared'] = $this->GTPProfileStore->count();
        $stdoutarray['GTP objects']['total_DGs'] = $gngtp;
        $stdoutarray['SCEP objects'] = array();
        $stdoutarray['SCEP objects']['shared'] = $this->SCEPProfileStore->count();
        $stdoutarray['SCEP objects']['total_DGs'] = $gnscep;
        $stdoutarray['PacketBroker objects'] = array();
        $stdoutarray['PacketBroker objects']['shared'] = $this->PacketBrokerProfileStore->count();
        $stdoutarray['PacketBroker objects']['total_DGs'] = $gnpacketbroker;

        $stdoutarray['SDWanErrorCorrection objects'] = array();
        $stdoutarray['SDWanErrorCorrection objects']['shared'] = $this->SDWanErrorCorrectionProfileStore->count();
        $stdoutarray['SDWanErrorCorrection objects']['total_DGs'] = $gnsdwanerrorcorrection;
        $stdoutarray['SDWanPathQuality objects'] = array();
        $stdoutarray['SDWanPathQuality objects']['shared'] = $this->SDWanPathQualityProfileStore->count();
        $stdoutarray['SDWanPathQuality objects']['total_DGs'] = $gnsdwanpathquality;
        $stdoutarray['SDWanSaasQuality objects'] = array();
        $stdoutarray['SDWanSaasQuality objects']['shared'] = $this->SDWanSaasQualityProfileStore->count();
        $stdoutarray['SDWanSaasQuality objects']['total_DGs'] = $gnsdwansaasquality;
        $stdoutarray['SDWanTrafficDistribution objects'] = array();
        $stdoutarray['SDWanTrafficDistribution objects']['shared'] = $this->SDWanTrafficDistributionProfileStore->count();
        $stdoutarray['SDWanTrafficDistribution objects']['total_DGs'] = $gnsdwantrafficdistribution;

        $stdoutarray['DataObjects objects']['shared'] = $this->DataObjectsProfileStore->count();
        $stdoutarray['DataObjects objects']['total_DGs'] = $gndataobjects;

        $stdoutarray['zones'] = $this->zoneStore->count();
        #$stdoutarray['apps'] = $this->appStore->count();

        /*
        $stdoutarray['interfaces'] = array();
        $stdoutarray['interfaces']['total'] = $numInterfaces;
        $stdoutarray['interfaces']['ethernet'] = $this->network->ethernetIfStore->count();

        $stdoutarray['sub-interfaces'] = array();
        $stdoutarray['sub-interfaces']['total'] = $numSubInterfaces;
        $stdoutarray['sub-interfaces']['ethernet'] = $this->network->ethernetIfStore->countSubInterfaces();
        */

        #PH::$JSON_TMP['all'] = $stdoutarray;
        PH::$JSON_TMP[] = $stdoutarray;

        if( !PH::$shadow_json )
            PH::print_stdout( $stdoutarray, true );

        //-----------------
        $stdoutarray = array();

        $stdoutarray['type'] = get_class( $this );

        $header = "Statistics for DG '" . PH::boldText('shared') . "'";
        $stdoutarray['header'] = $header;

        $stdoutarray['security rules'] = array();
        $stdoutarray['security rules']['pre'] = $this->securityRules->countPreRules();
        $stdoutarray['security rules']['post'] = $this->securityRules->countPostRules();

        $stdoutarray['nat rules'] = array();
        $stdoutarray['nat rules']['pre'] = $this->natRules->countPreRules();
        $stdoutarray['nat rules']['post'] = $this->natRules->countPostRules();

        $stdoutarray['qos rules'] = array();
        $stdoutarray['qos rules']['pre'] = $this->qosRules->countPreRules();
        $stdoutarray['qos rules']['post'] = $this->qosRules->countPostRules();

        $stdoutarray['pbf rules'] = array();
        $stdoutarray['pbf rules']['pre'] = $this->pbfRules->countPreRules();
        $stdoutarray['pbf rules']['post'] = $this->pbfRules->countPostRules();

        $stdoutarray['decrypt rules'] = array();
        $stdoutarray['decrypt rules']['pre'] = $this->decryptionRules->countPreRules();
        $stdoutarray['decrypt rules']['post'] = $this->decryptionRules->countPostRules();

        $stdoutarray['app-override rules'] = array();
        $stdoutarray['app-override rules']['pre'] = $this->appOverrideRules->countPreRules();
        $stdoutarray['app-override rules']['post'] = $this->appOverrideRules->countPostRules();

        $stdoutarray['captive-portal rules'] = array();
        $stdoutarray['captive-portal rules']['pre'] = $this->captivePortalRules->countPreRules();
        $stdoutarray['captive-portal rules']['post'] = $this->captivePortalRules->countPostRules();

        $stdoutarray['authentication rules'] = array();
        $stdoutarray['authentication rules']['pre'] = $this->authenticationRules->countPreRules();
        $stdoutarray['authentication rules']['post'] = $this->authenticationRules->countPostRules();

        $stdoutarray['dos rules'] = array();
        $stdoutarray['dos rules']['pre'] = $this->dosRules->countPreRules();
        $stdoutarray['dos rules']['post'] = $this->dosRules->countPostRules();

        $stdoutarray['tunnel-inspection rules'] = array();
        $stdoutarray['tunnel-inspection rules']['pre'] = $this->tunnelInspectionRules->countPreRules();
        $stdoutarray['tunnel-inspection rules']['post'] = $this->tunnelInspectionRules->countPostRules();

        $stdoutarray['default-security rules'] = array();
        $stdoutarray['default-security rules']['pre'] = $this->defaultSecurityRules->countPreRules();
        $stdoutarray['default-security rules']['post'] = $this->defaultSecurityRules->countPostRules();

        $stdoutarray['network-packet-broker rules'] = array();
        $stdoutarray['network-packet-broker rules']['pre'] = $this->networkPacketBrokerRules->countPreRules();
        $stdoutarray['network-packet-broker rules']['post'] = $this->networkPacketBrokerRules->countPostRules();

        $stdoutarray['sdwan rules'] = array();
        $stdoutarray['sdwan rules']['pre'] = $this->sdWanRules->countPreRules();
        $stdoutarray['sdwan rules']['post'] = $this->sdWanRules->countPostRules();

        $stdoutarray['address objects'] = array();
        $stdoutarray['address objects']['total'] = $this->addressStore->count();
        $stdoutarray['address objects']['address'] = $this->addressStore->countAddresses();
        $stdoutarray['address objects']['group'] = $this->addressStore->countAddressGroups();
        $stdoutarray['address objects']['tmp'] = $this->addressStore->countTmpAddresses();
        $stdoutarray['address objects']['region'] = $this->addressStore->countRegionObjects();
        $stdoutarray['address objects']['unused'] = $this->addressStore->countUnused();

        $stdoutarray['service objects'] = array();
        $stdoutarray['service objects']['total'] = $this->serviceStore->count();
        $stdoutarray['service objects']['service'] = $this->serviceStore->countServices();
        $stdoutarray['service objects']['group'] = $this->serviceStore->countServiceGroups();
        $stdoutarray['service objects']['tmp'] = $this->serviceStore->countTmpServices();
        $stdoutarray['service objects']['unused'] = $this->serviceStore->countUnused();

        $stdoutarray['tag objects'] = array();
        $stdoutarray['tag objects']['total'] = $this->tagStore->count();
        $stdoutarray['tag objects']['unused'] = $this->tagStore->countUnused();

        $stdoutarray['securityProfileGroup objects'] = array();
        $stdoutarray['securityProfileGroup objects']['total'] = $this->securityProfileGroupStore->count();

        $stdoutarray['Anti-Spyware objects'] = array();
        $stdoutarray['Anti-Spyware objects']['total'] = $this->AntiSpywareProfileStore->count();
        $stdoutarray['Vulnerability objects'] = array();
        $stdoutarray['Vulnerability objects']['total'] = $this->VulnerabilityProfileStore->count();
        $stdoutarray['Antivirus objects'] = array();
        $stdoutarray['Antivirus objects']['total'] = $this->AntiVirusProfileStore->count();
        $stdoutarray['Wildfire objects'] = array();
        $stdoutarray['Wildfire objects']['total'] = $this->WildfireProfileStore->count();
        $stdoutarray['URL objects'] = array();
        $stdoutarray['URL objects']['total'] = $this->URLProfileStore->count();
        $stdoutarray['custom URL objects'] = array();
        $stdoutarray['custom URL objects']['total'] = $this->customURLProfileStore->count();
        $stdoutarray['File-Blocking objects'] = array();
        $stdoutarray['File-Blocking objects']['total'] = $this->FileBlockingProfileStore->count();
        $stdoutarray['Decryption objects'] = array();
        $stdoutarray['Decryption objects']['total'] = $this->DecryptionProfileStore->count();

        $stdoutarray['HipObject objects'] = array();
        $stdoutarray['HipObject objects']['total'] = $this->HipObjectsProfileStore->count();
        $stdoutarray['HipProfile objects'] = array();
        $stdoutarray['HipProfile objects']['total'] = $this->HipProfilesProfileStore->count();

        $stdoutarray['GTP objects'] = array();
        $stdoutarray['GTP objects']['total'] = $this->GTPProfileStore->count();
        $stdoutarray['SCEP objects'] = array();
        $stdoutarray['SCEP objects']['total'] = $this->SCEPProfileStore->count();
        $stdoutarray['PacketBroker objects'] = array();
        $stdoutarray['PacketBroker objects']['total'] = $this->PacketBrokerProfileStore->count();

        $stdoutarray['SDWanErrorCorrection objects'] = array();
        $stdoutarray['SDWanErrorCorrection objects']['total'] = $this->SDWanErrorCorrectionProfileStore->count();
        $stdoutarray['SDWanPathQuality objects'] = array();
        $stdoutarray['SDWanPathQuality objects']['total'] = $this->SDWanPathQualityProfileStore->count();
        $stdoutarray['SDWanSaasQuality objects'] = array();
        $stdoutarray['SDWanSaasQuality objects']['total'] = $this->SDWanSaasQualityProfileStore->count();
        $stdoutarray['SDWanTrafficDistribution objects'] = array();
        $stdoutarray['SDWanTrafficDistribution objects']['total'] = $this->SDWanTrafficDistributionProfileStore->count();

        $stdoutarray['DataObjects objects'] = array();
        $stdoutarray['DataObjects objects']['total'] = $this->DataObjectsProfileStore->count();

        #PH::$JSON_TMP['shared'] = $stdoutarray;
        PH::$JSON_TMP[] = $stdoutarray;

        if( !PH::$shadow_json )
            PH::print_stdout( $stdoutarray, true );
    }

    public function API_load_from_running(PanAPIConnector $conn)
    {
        $this->connector = $conn;

        $xmlDoc = $this->connector->getRunningConfig();
        $this->load_from_domxml($xmlDoc);
    }

    public function API_load_from_candidate(PanAPIConnector $conn)
    {
        $this->connector = $conn;

        $xmlDoc = $this->connector->getCandidateConfig();
        $this->load_from_domxml($xmlDoc);
    }

    /**
     * send current config to the firewall and save under name $config_name
     * @param $config_filename string filename you want to save config in PANOS
     */
    public function API_uploadConfig($config_filename = 'panconfigurator-default.xml')
    {
        PH::print_stdout(  "Uploadig config to device...." );

        $url = "&type=import&category=configuration&category=configuration";
        $this->connector->sendRequest($url, FALSE, DH::dom_to_xml($this->xmlroot), $config_filename);


    }

    /**
     *    load all managed firewalls configs from API from running config if $fromRunning = TRUE
     */
    public function API_loadManagedFirewallConfigs($fromRunning)
    {
        $this->managedFirewalls = array();

        $connector = findConnectorOrDie($this);

        foreach( $this->managedFirewallsSerials as $serial )
        {
            $fw = new PANConf($this, $serial);
            $fw->panorama = $this;
            $newCon = new PanAPIConnector($connector->apihost,
                $connector->apikey,
                'panos-via-panorama',
                $serial,
                $connector->port);
            $fw->API_load_from_candidate($newCon);
        }

    }

    /**
     *    load all managed firewalls configs from a directory
     * @var string $fromDirectory
     */
    public function loadManagedFirewallsConfigs($fromDirectory = './')
    {
        $this->managedFirewalls = array();

        $files = scandir($fromDirectory);

        foreach( $this->managedFirewallsSerials as &$serial )
        {
            $fw = FALSE;
            foreach( $files as &$file )
            {
                $pos = strpos($file, $serial);
                if( $pos !== FALSE )
                {
                    //$fc = file_get_contents($file);
                    //if( $fc === FALSE )
                    //	derr("could not open file '$file'");

                    PH::print_stdout(  "Loading FW '$serial' from file '$file'.");

                    $fw = new PANConf($this, $serial);
                    $fw->panorama = $this;
                    $fw->load_from_file($fromDirectory . '/' . $file);
                    $this->managedFirewalls[] = $fw;
                    break;
                }

            }
            if( $fw === FALSE )
            {
                derr("couldn't find a suitable file to load for FW '$serial'");
            }
        }

        //derr('not implemented yet');
    }


    /**
     * @param string $deviceSerial
     * @param string $vsysName
     * @return DeviceGroup|bool
     */
    public function findApplicableDGForVsys($deviceSerial, $vsysName)
    {
        if( $deviceSerial === null || strlen($deviceSerial) < 1 )
            derr('invalid serial provided!');
        if( $vsysName === null || strlen($vsysName) < 1 )
            derr('invalid vsys provided!');

        //PH::print_stdout(  "looking for serial $deviceSerial  and vsys $vsysName" );

        foreach( $this->deviceGroups as $dv )
        {
            $ds = $dv->getDevicesInGroup();
            foreach( $ds as &$d )
            {
                if( $d['serial'] == $deviceSerial )
                {
                    //PH::print_stdout(  "serial found" );
                    if( array_search($vsysName, $d['vsyslist']) !== FALSE )
                    {
                        //PH::print_stdout(  "match!" );
                        return $dv;
                    }
                }
            }
        }

        return FALSE;
    }

    /**
     * Create a blank device group. Return that DV object.
     * @param string $name
     * @return DeviceGroup
     **/
    public function createDeviceGroup($name, $parentDGname = null )
    {
        $newDG = new DeviceGroup($this);
        $newDG->load_from_templateXml();
        $newDG->setName($name);

        $this->deviceGroups[] = $newDG;

        if( $this->version >= 70 )
        {
            if( $this->version >= 80 )
                $dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/max-internal-id', $this->xmlroot);
            else
                $dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/dg-meta-data/max-dg-id', $this->xmlroot);

            $dgMaxID = $dgMetaDataNode->textContent;
            $dgMaxID++;
            DH::setDomNodeText($dgMetaDataNode, "{$dgMaxID}");

            if( $this->version >= 80 )
                $dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/devices/entry[@name="localhost.localdomain"]/device-group', $this->xmlroot);
            else
                $dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/dg-meta-data/dg-info', $this->xmlroot);

            $parentXMLnode = "";
            if( $parentDGname !== null )
            {
                $parentDG = $this->findDeviceGroup( $parentDGname );
                if( $parentDG === null )
                    mwarning("DeviceGroup '$name' has DeviceGroup '{$parentDGname}' listed as parent but it cannot be found in XML");
                else
                    $parentXMLnode = "<parent-dg>".$parentDGname."</parent-dg>";
            }
            if( $this->version >= 80 )
                $newXmlNode = DH::importXmlStringOrDie($this->xmldoc, "<entry name=\"{$name}\"><id>{$dgMaxID}</id>".$parentXMLnode."</entry>");
            else
                $newXmlNode = DH::importXmlStringOrDie($this->xmldoc, "<entry name=\"{$name}\"><dg-id>{$dgMaxID}</dg-id>".$parentXMLnode."</entry>");

            $dgMetaDataNode->appendChild($newXmlNode);
        }

        if( $parentDGname !== null )
        {
            $parentDG = $this->findDeviceGroup( $parentDGname );
            if( $parentDG === null )
                mwarning("DeviceGroup '$name' has DeviceGroup '{$parentDGname}' listed as parent but it cannot be found in XML");
            else
            {
                $parentDG->_childDeviceGroups[$name] = $newDG;
                /*
                $newDG->parentDeviceGroup = $parentDG;
                $newDG->addressStore->parentCentralStore = $parentDG->addressStore;
                $newDG->serviceStore->parentCentralStore = $parentDG->serviceStore;
                $newDG->tagStore->parentCentralStore = $parentDG->tagStore;
                $newDG->scheduleStore->parentCentralStore = $parentDG->scheduleStore;
                $newDG->appStore->parentCentralStore = $parentDG->appStore;
                $newDG->securityProfileGroupStore->parentCentralStore = $parentDG->securityProfileGroupStore;
                */
                //Todo: swaschkut 20210505 - check if other Stores must be added
                //- appStore;scheduleStore/securityProfileGroupStore/all kind of SecurityProfile

                $storeType = array(
                    'addressStore', 'serviceStore', 'tagStore', 'scheduleStore', 'appStore',

                    'securityProfileGroupStore',

                    'URLProfileStore', 'AntiVirusProfileStore', 'FileBlockingProfileStore', 'DataFilteringProfileStore',
                    'VulnerabilityProfileStore', 'AntiSpywareProfileStore', 'WildfireProfileStore',
                    'DecryptionProfileStore', 'HipObjectsProfileStore'

                );

                foreach( $storeType as $type )
                    $newDG->$type->parentCentralStore = $parentDG->$type;
            }
        }

        return $newDG;
    }

    /**
     * Remove a device group.
     * @param DeviceGroup $DG
     **/
    public function removeDeviceGroup( $DG )
    {
        $DGname = $DG->name();
        $childDGs = $DG->_childDeviceGroups;
        if( count( $childDGs ) !== 0 )
        {
            mwarning("DeviceGroup '$DGname' has ChildDGs. Delete of DG not possible.");
            return;
        }
        else
        {
            //remove DG from XML
            $xPath = "/config/devices/entry[@name='localhost.localdomain']/device-group";
            $dgNode = DH::findXPathSingleEntryOrDie($xPath, $this->xmlroot);

            $DGremove = DH::findFirstElementByNameAttrOrDie('entry', $DGname, $dgNode);
            $dgNode->removeChild( $DGremove );

            //remove DG from DG Meta
            if( $this->version >= 80 )
                $dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/devices/entry[@name="localhost.localdomain"]/device-group', $this->xmlroot);
            else
                $dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/dg-meta-data/dg-info', $this->xmlroot);

            $DGmetaData = DH::findFirstElementByNameAttrOrDie('entry', $DGname, $dgMetaDataNode);
            $dgMetaDataNode->removeChild( $DGmetaData );

            unset($this->deviceGroups[ $DGname ]);
        }


        //API: send empty DG node
        /*
        $xpath = "/config/devices/entry[@name='localhost.localdomain']/device-group/entry[@name='".$objectsLocation."']";

        $apiArgs = Array();
        $apiArgs['type'] = 'config';
        $apiArgs['action'] = 'delete';
        $apiArgs['xpath'] = &$xpath;

        PH::print_stdout(  "     "."*** delete each member from ".$entry." " );

        if( $configInput['type'] == 'api' )
            $response = $pan->connector->sendRequest($apiArgs);
         */


    }
    /**
     * @return DeviceGroup[]
     */
    public function getDeviceGroups()
    {
        return $this->deviceGroups;
    }


    /**
     * Create a blank template. Return that template object.
     * @param string $name
     * @return Template
     **/
    public function createTemplate($name)
    {
        $newTemplate = new Template($name, $this);
        $newTemplate->load_from_templateXml();
        $newTemplate->setName($name);

        $this->templates[] = $newTemplate;


        if( $this->version >= 70 )
        {
            if( $this->version >= 80 )
                $tempMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/max-internal-id', $this->xmlroot);
            else
            {
                //not available for template in version >= 70 and < 80
                #$dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/dg-meta-data/max-dg-id', $this->xmlroot);
            }


            $tempMaxID = $tempMetaDataNode->textContent;
            $tempMaxID++;
            DH::setDomNodeText($tempMetaDataNode, "{$tempMaxID}");

            if( $this->version >= 80 )
                $tempMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/devices/entry[@name="localhost.localdomain"]/template', $this->xmlroot);
            else
            {
                //not available for template in version >= 70 and < 80
                #$dgMetaDataNode = DH::findXPathSingleEntryOrDie('/config/readonly/dg-meta-data/dg-info', $this->xmlroot);
            }


            if( $this->version >= 80 )
                $newXmlNode = DH::importXmlStringOrDie($this->xmldoc, "<entry name=\"{$name}\"><id>{$tempMaxID}</id></entry>");
            else
            {
                //not available for template in version >= 70 and < 80
                #$newXmlNode = DH::importXmlStringOrDie($this->xmldoc, "<entry name=\"{$name}\"><dg-id>{$tempMaxID}</dg-id></entry>");
            }


            $tempMetaDataNode->appendChild($newXmlNode);
        }


        return $newTemplate;
    }

    /**
     * Remove a template.
     * @param Template $template
     **/
    public function removeTemplate( $template )
    {
        $Templatename = $template->name();

        #$template->display_references();

        /*
         * //warning if template is used in TemplateStack
         * //implementation missing also in actions-device line 294
         * DeviceCallContext::$supportedActions['Template-delete'] = array(
         *
        $childDGs = $DG->_childDeviceGroups;
        if( count( $childDGs ) !== 0 )
        {
            mwarning("DeviceGroup '$DGname' has ChildDGs. Delete of DG not possible.");
            return;
        }
        else
        {
        */
            //remove Template from XML
            $xPath = "/config/devices/entry[@name='localhost.localdomain']/template";
            $dgNode = DH::findXPathSingleEntryOrDie($xPath, $this->xmlroot);

            $remove = DH::findFirstElementByNameAttrOrDie('entry', $Templatename, $dgNode);
            $dgNode->removeChild( $remove );


        unset($this->templates[$Templatename]);
            //Todo: cleanup memory
        //}
    }


    /**
     * Remove a template.
     * @param TemplateStack $templateStack
     **/
    public function removeTemplateStack( $templateStack )
    {
        $TemplateStackname = $templateStack->name();

        #$templateStack->display_references();

        /*
         * //warning if template is used in TemplateStack
         * //implementation missing also in actions-device line 294
         * DeviceCallContext::$supportedActions['Template-delete'] = array(
         *
        $childDGs = $DG->_childDeviceGroups;
        if( count( $childDGs ) !== 0 )
        {
            mwarning("DeviceGroup '$DGname' has ChildDGs. Delete of DG not possible.");
            return;
        }
        else
        {
        */
        //remove TemplateSTack from XML
        $xPath = "/config/devices/entry[@name='localhost.localdomain']/template-stack";
        $dgNode = DH::findXPathSingleEntryOrDie($xPath, $this->xmlroot);

        $remove = DH::findFirstElementByNameAttrOrDie('entry', $TemplateStackname, $dgNode);
        $dgNode->removeChild( $remove );

        unset($this->templatestacks[$TemplateStackname]);
        
        //}
    }

    /**
     * @return Template[]
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @return TemplateStack[]
     */
    public function getTemplatesStacks()
    {
        return $this->templatestacks;
    }
    public function isPanorama()
    {
        return TRUE;
    }

    public function findSubSystemByName($location)
    {
        return $this->findDeviceGroup($location);
    }

    public function childDeviceGroups()
    {
        return $this->getDeviceGroups();
    }
}



