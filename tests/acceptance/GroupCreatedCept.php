<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1524, 1024);
$I->wantTo('can\'t create group if it\'s already created');

// GF group already created
$I->amOnPage('/');
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','elen');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/elen/');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Group', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Group']");
$I->dontSee('Create My Group');

// Check group created 
$I->seeElement('//ul[@id="wp-admin-bar-my-account-groups-default"]/li[@id="wp-admin-bar-my-account-group-created"]/div[@class="ab-item ab-empty-item"]');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Logout');
$I->click('Logout', '.logout');
$I->wait(3);
//--
