<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Test behaviour when user cant create a group');

//Member cannot create a group
$I->amOnPage('/');
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Group', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Group']");
$I->dontSeeElement('//ul[@id="wp-admin-bar-my-account-groups-default"]/li[@id="wp-admin-bar-my-account-groups-create"]/a[@class="ab-item"');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Logout');
$I->click('Logout', '.logout');
$I->wait(3);
//--