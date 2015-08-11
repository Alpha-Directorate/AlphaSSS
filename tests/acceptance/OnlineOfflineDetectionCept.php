<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1524, 1024);
$I->wantTo('check default offline settings');
$I->amOnPage('/');
$I->see('Browse','li');
$I->click("//a[text()='Browse']");
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/div[@class='activity']/div[@class='member-offline']");

// Check online detection
$I->wantTo('see how online detection is works');
$elen = $I->haveFriend('elen');
$elen->does(function(AcceptanceTester $I) {
	$I->amOnPage('/');
	$I->resizeWindow(1524, 1024);
	$I->see('Login','.login');
	$I->click("//a[@class='login' and text()='Login']");
	$I->seeCurrentUrlEquals('/wp/wp-login.php');
	$I->fillField('log','elen');
	$I->fillField('pwd','funkadelicbro87');
	$I->click('Log In');
	$I->seeCurrentUrlEquals('/browse/elen/');
});
$I->wait(8);
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/div[@class='activity']/div[@class='member-online']");
//--

// Check offline detection
$elen->does(function(AcceptanceTester $I) {
	$I->seeCurrentUrlEquals('/browse/elen/');
	$I->moveMouseOver("//div[@class='header-account-login']");
	$I->see('Logout');
	$I->click('Logout', '.logout');
	$I->seeCurrentUrlEquals('/browse/elen/?loggedout=true');
	$I->see('Login','.login');
});
$I->wait(3);
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/div[@class='activity']/div[@class='member-offline']");
//--