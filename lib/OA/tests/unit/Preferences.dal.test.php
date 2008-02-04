<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/lib/OA/Preferences.php';
require_once MAX_PATH . '/lib/OA/Dal/DataGenerator.php';

/**
 * A class for testing the core OA_Preferences class.
 *
 * @package    OpenadsPermission
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew.hill@openx.org>
 */
class Test_OA_Preferences extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Test_OA_Preferences()
    {
        $this->UnitTestCase();
    }

    /**
     * A method to test the OA_Preferences::loadPreferences() method
     * when the preferences should be loaded in a one-dimensional
     * array.
     */
    function testLoadPreferencesOneDimensional()
    {
        // Test 1: Test with no user logged in, and ensure that no
        //         preferences are loaded.
        unset($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Test 2: Test with no user logged in, and ensure that no
        //         preferences are loaded, and that esisting preferences
        //         that may exist are removed.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Create the admin account
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_name = 'Administrator Account';
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);

        // Create a user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->contact_name = 'Andrew Hill';
        $doUsers->email_address = 'andrew.hill@openads.org';
        $doUsers->username = 'admin';
        $doUsers->password = md5('password');
        $doUsers->default_account_id = $adminAccountId;
        $userId = DataGenerator::generateOne($doUsers);

        // Create admin association
        $doAUA = OA_Dal::factoryDO('account_user_assoc');
        $doAUA->account_id = $adminAccountId;
        $doAUA->user_id = $userId;
        $doAUA->insert();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 3: Test with the admin account logged in, but no preferences in
        //         the system, and ensure that no preferences are loaded, and
        //         that esisting preferences that may exist are removed.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Prepare a fake preference
        $doPreferences = OA_Dal::factoryDO('preferences');
        $doPreferences->preference_name = 'preference_1';
        $doPreferences->account_type = OA_ACCOUNT_ADMIN;
        $preferenceId = DataGenerator::generateOne($doPreferences);

        // Test 4: Test with the admin user logged in, and a preference in
        //         the system, but no preference values set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Insert a fake preference value
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $adminAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'foo!';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 5: Test with the admin account logged in, a preference in the
        //         system, and a preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 1);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');

        // Prepare a second fake preference
        $doPreferences = OA_Dal::factoryDO('preferences');
        $doPreferences->preference_name = 'preference_2';
        $doPreferences->account_type = OA_ACCOUNT_MANAGER;
        $preferenceId = DataGenerator::generateOne($doPreferences);

        // Test 6: Test with the admin account logged in, two preferences in the
        //         system, and one preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 1);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');

        // Insert a second fake preference value
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $adminAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'bar!';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 7: Test with the admin account logged in, two preferences in the
        //         system, and two preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'bar!');

        // Create a manager "agency" and account
        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->name = 'Manager Account';
        $doAgency->contact = 'Andrew Hill';
        $doAgency->email = 'andrew.hill@openads.org';
        $managerAgencyId = DataGenerator::generateOne($doAgency);

        // Get the account ID for the manager "agency"
        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->agency_id = $managerAgencyId;
        $doAgency->find();
        $doAgency->fetch();
        $aAgency = $doAgency->toArray();
        $managerAccountId = $aAgency['account_id'];

        // Update the existing user to log into the manager account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 8: Test with the manager account logged in, two preferences in the
        //         system, and two preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'bar!');

        // Overwrite preference_2 at the manager account level
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $managerAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'baz!';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 9: Test with the manager account logged in, two preferences in the
        //         system, two preference value set for the admin account, with
        //         one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'baz!');

        // Update the existing user to log into the admin account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $adminAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 10: Test with the admin account logged in, two preferences in the
        //          system, two preference value set for the admin account, with
        //          one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'bar!');

        // Create an advertiser "client" and account, owned by the manager
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->name = 'Advertiser Account';
        $doClients->contact = 'Andrew Hill';
        $doClients->email = 'andrew.hill@openads.org';
        $doClients->agencyid = $managerAgencyId;
        $advertiserClientId = DataGenerator::generateOne($doClients);

        // Get the account ID for the advertiser "client"
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->clientid = $advertiserClientId;
        $doClients->find();
        $doClients->fetch();
        $aAdvertiser = $doClients->toArray();
        $advertiserAccountId = $aAdvertiser['account_id'];

        // Update the existing user to log into the advertiser account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $advertiserAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 11: Test with the advertiser account logged in, two preferences in the
        //          system, two preference value set for the admin account, with
        //          one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'baz!');

        // Prepare a third fake preference
        $doPreferences = OA_Dal::factoryDO('preferences');
        $doPreferences->preference_name = 'preference_3';
        $doPreferences->account_type = OA_ACCOUNT_ADVERTISER;
        $preferenceId = DataGenerator::generateOne($doPreferences);

        // Insert a third fake preference value
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $adminAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'Admin Preference for Preference 3';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 12: Test with the advertiser account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'baz!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3'], 'Admin Preference for Preference 3');

        // Overwrite preference_3 at the advertiser account level
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $advertiserAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'Advertiser Preference for Preference 3';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 13: Test with the advertiser account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account, and another
        //          overwritten by the advertiser account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'baz!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3'], 'Advertiser Preference for Preference 3');

        // Update the existing user to log into the manager account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 14: Test with the manager account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account, and another
        //          overwritten by the advertiser account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'baz!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3'], 'Admin Preference for Preference 3');

        // Update the existing user to log into the admin account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $adminAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 14: Test with the admin account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account, and another
        //          overwritten by the advertiser account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences();
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2'], 'bar!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3'], 'Admin Preference for Preference 3');

        DataGenerator::cleanUp();
    }

    /**
     * A method to test the OA_Preferences::loadPreferences() method
     * when the preferences should be loaded in a two-dimensional
     * array.
     */
    function testLoadPreferencesTwoDimensional()
    {
        // Test 1: Test with no user logged in, and ensure that no
        //         preferences are loaded.
        unset($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Test 2: Test with no user logged in, and ensure that no
        //         preferences are loaded, and that esisting preferences
        //         that may exist are removed.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Create the admin account
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_name = 'Administrator Account';
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);

        // Create a user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->contact_name = 'Andrew Hill';
        $doUsers->email_address = 'andrew.hill@openads.org';
        $doUsers->username = 'admin';
        $doUsers->password = md5('password');
        $doUsers->default_account_id = $adminAccountId;
        $userId = DataGenerator::generateOne($doUsers);

        // Create admin association
        $doAUA = OA_Dal::factoryDO('account_user_assoc');
        $doAUA->account_id = $adminAccountId;
        $doAUA->user_id = $userId;
        $doAUA->insert();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 3: Test with the admin account logged in, but no preferences in
        //         the system, and ensure that no preferences are loaded, and
        //         that esisting preferences that may exist are removed.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Prepare a fake preference
        $doPreferences = OA_Dal::factoryDO('preferences');
        $doPreferences->preference_name = 'preference_1';
        $doPreferences->account_type = OA_ACCOUNT_ADMIN;
        $preferenceId = DataGenerator::generateOne($doPreferences);

        // Test 4: Test with the admin user logged in, and a preference in
        //         the system, but no preference values set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNull($GLOBALS['_MAX']['PREF']);

        // Insert a fake preference value
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $adminAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'foo!';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 5: Test with the admin account logged in, a preference in the
        //         system, and a preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 1);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');

        // Prepare a second fake preference
        $doPreferences = OA_Dal::factoryDO('preferences');
        $doPreferences->preference_name = 'preference_2';
        $doPreferences->account_type = OA_ACCOUNT_MANAGER;
        $preferenceId = DataGenerator::generateOne($doPreferences);

        // Test 6: Test with the admin account logged in, two preferences in the
        //         system, and one preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 1);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);

        // Insert a second fake preference value
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $adminAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'bar!';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 7: Test with the admin account logged in, two preferences in the
        //         system, and two preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'bar!');

        // Create a manager "agency" and account
        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->name = 'Manager Account';
        $doAgency->contact = 'Andrew Hill';
        $doAgency->email = 'andrew.hill@openads.org';
        $managerAgencyId = DataGenerator::generateOne($doAgency);

        // Get the account ID for the manager "agency"
        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->agency_id = $managerAgencyId;
        $doAgency->find();
        $doAgency->fetch();
        $aAgency = $doAgency->toArray();
        $managerAccountId = $aAgency['account_id'];

        // Update the existing user to log into the manager account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 8: Test with the manager account logged in, two preferences in the
        //         system, and two preference value set for the admin account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'bar!');

        // Overwrite preference_2 at the manager account level
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $managerAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'baz!';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 9: Test with the manager account logged in, two preferences in the
        //         system, two preference value set for the admin account, with
        //         one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'baz!');

        // Update the existing user to log into the admin account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $adminAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 10: Test with the admin account logged in, two preferences in the
        //          system, two preference value set for the admin account, with
        //          one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'bar!');

        // Create an advertiser "client" and account, owned by the manager
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->name = 'Advertiser Account';
        $doClients->contact = 'Andrew Hill';
        $doClients->email = 'andrew.hill@openads.org';
        $doClients->agencyid = $managerAgencyId;
        $advertiserClientId = DataGenerator::generateOne($doClients);

        // Get the account ID for the advertiser "client"
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->clientid = $advertiserClientId;
        $doClients->find();
        $doClients->fetch();
        $aAdvertiser = $doClients->toArray();
        $advertiserAccountId = $aAdvertiser['account_id'];

        // Update the existing user to log into the advertiser account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $advertiserAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 11: Test with the advertiser account logged in, two preferences in the
        //          system, two preference value set for the admin account, with
        //          one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'baz!');

        // Prepare a third fake preference
        $doPreferences = OA_Dal::factoryDO('preferences');
        $doPreferences->preference_name = 'preference_3';
        $doPreferences->account_type = OA_ACCOUNT_ADVERTISER;
        $preferenceId = DataGenerator::generateOne($doPreferences);

        // Insert a third fake preference value
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $adminAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'Admin Preference for Preference 3';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 12: Test with the advertiser account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'baz!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_3']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_3']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['account_type'], OA_ACCOUNT_ADVERTISER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['value'], 'Admin Preference for Preference 3');

        // Overwrite preference_3 at the advertiser account level
        $doAccount_Preference_Assoc = OA_Dal::factoryDO('account_preference_assoc');
        $doAccount_Preference_Assoc->account_id = $advertiserAccountId;
        $doAccount_Preference_Assoc->preference_id = $preferenceId;
        $doAccount_Preference_Assoc->value = 'Advertiser Preference for Preference 3';
        DataGenerator::generateOne($doAccount_Preference_Assoc);

        // Test 13: Test with the advertiser account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account, and another
        //          overwritten by the advertiser account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'baz!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_3']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_3']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['account_type'], OA_ACCOUNT_ADVERTISER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['value'], 'Advertiser Preference for Preference 3');

        // Update the existing user to log into the manager account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 14: Test with the manager account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account, and another
        //          overwritten by the advertiser account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'baz!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_3']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_3']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['account_type'], OA_ACCOUNT_ADVERTISER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['value'], 'Admin Preference for Preference 3');

        // Update the existing user to log into the admin account by default
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $doUsers->default_account_id = $adminAccountId;
        $doUsers->update();

        // Ensure this user is "logged in"
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->username = 'admin';
        $doUsers->find();
        $doUsers->fetch();
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        // Test 14: Test with the admin account logged in, three preferences in the
        //          system, three preference value set for the admin account, with
        //          one of them overwritten by the manager account, and another
        //          overwritten by the advertiser account.
        $GLOBALS['_MAX']['PREF'] = array('foo' => 'bar');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        OA_Preferences::loadPreferences(true);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']), 3);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_1']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_1']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['account_type'], OA_ACCOUNT_ADMIN);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_1']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_1']['value'], 'foo!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_2']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_2']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['account_type'], OA_ACCOUNT_MANAGER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_2']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_2']['value'], 'bar!');
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']);
        $this->assertTrue(is_array($GLOBALS['_MAX']['PREF']['preference_3']));
        $this->assertEqual(count($GLOBALS['_MAX']['PREF']['preference_3']), 2);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['account_type']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['account_type'], OA_ACCOUNT_ADVERTISER);
        $this->assertNotNull($GLOBALS['_MAX']['PREF']['preference_3']['value']);
        $this->assertEqual($GLOBALS['_MAX']['PREF']['preference_3']['value'], 'Admin Preference for Preference 3');

        DataGenerator::cleanUp();
    }

}

?>