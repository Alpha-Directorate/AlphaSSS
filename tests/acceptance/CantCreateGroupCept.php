<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Test behaviour when user cant create a group');

//Member cannot create a group
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Group', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Group']");
$I->dontSeeElement('//ul[@id="wp-admin-bar-my-account-groups-default"]/li[@id="wp-admin-bar-my-account-groups-create"]/a[@class="ab-item"');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");
$I->wait(3);
//--