<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Group creation for GF');
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','nadya');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/nadya/');
$I->seeElement('#wp-admin-bar-user-credits-icon');
$I->moveMouseOver("//a[@title='My Account']");
$I->see('Group', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Group']");
$I->click('Create My Group');
$I->seeCurrentUrlEquals('/groups/create/step/group-details/');
$I->see('1. Details');
$I->see('2. Photo');
$I->see('3. Invites');

// Check pre definded group name
$I->seeInField('#group-name', "nadya's Group");

$I->amGoingTo('check group description javascript');
$I->see('140 characters left', '#about-count');
$text = " AlphaSSS ";
$I->fillField('#group-desc', $text);
$I->see((140 - strlen($text)) . ' characters left', '#about-count');
$I->fillField('#group-desc',  str_repeat($text, 100));
$I->see('0 characters left', '#about-count');

// Tooltips tests
$I->dontSee('Group Name Create Tooltip');
$I->seeElement('//label[@for="group-name"]/ul/li[2]/div[@class="alphasss-tooltip"]');
$I->moveMouseOver('//label[@for="group-name"]/ul/li[2]/div[@class="alphasss-tooltip"]');
$I->see('Group Name Create Tooltip');

$I->dontSee('Group Description Create Tooltip');
$I->seeElement('//label[@for="group-desc"]/ul/li[2]/div[@class="alphasss-tooltip"]');
$I->moveMouseOver('//label[@for="group-desc"]/ul/li[2]/div[@class="alphasss-tooltip"]');
$I->see('Group Description Create Tooltip');
//--

$I->seeElement('//input[@id="group-creation-create" and @value="Create My Group and Continue"]');
$I->click('//input[@id="group-creation-create" and @value="Create My Group and Continue"]');
$I->seeCurrentUrlEquals('/groups/create/step/group-avatar/');
$I->click('//input[@id="group-creation-next"]');
$I->seeCurrentUrlEquals('/groups/create/step/group-invites/');
$I->click('//input[@id="group-creation-finish"]');
$I->seeCurrentUrlEquals('/groups/nadyas-group/');
$I->seeElement('#nav-forum');
$I->moveMouseOver("//a[@title='My Account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");
$I->wait(3);