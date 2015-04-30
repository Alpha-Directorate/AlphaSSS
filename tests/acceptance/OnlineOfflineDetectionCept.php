<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('check default offline settings');
$I->amOnPage('/');
$I->see('Browse','li');
$I->click("//a[text()='Browse']");
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/span[@class='activity']/div[@class='member-offline']");

// Check online detection
$I->wantTo('see how online detection is works');
$elen = $I->haveFriend('elen');
$elen->does(function(AcceptanceTester $I) {
	$I->amOnPage('/');
	$I->resizeWindow(1024, 1024);
	$I->see('Login','.button');
	$I->click("//a[@class='button' and text()='Login']");
	$I->seeCurrentUrlEquals('/wp/wp-login.php');
	$I->fillField('log','elen');
	$I->fillField('pwd','funkadelicbro87');
	$I->click('Log In');
	$I->seeCurrentUrlEquals('/browse/elen/');
});
$I->wait(3);
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/span[@class='activity']/div[@class='member-online']");
//--

// Check offline detection
$I->wantTo('see how offline detection is works');
$elen->does(function(AcceptanceTester $I) {
	$I->seeCurrentUrlEquals('/browse/elen/');
	$I->moveMouseOver("//a[@title='My Account']");
	$I->see('Log Out','.ab-item');
	$I->click("//a[@class='ab-item' and text()='Log Out']");
	$I->seeCurrentUrlEquals('/browse/elen/?loggedout=true');
	$I->see('Login','.button');
});
$I->wait(3);
$I->seeElement("//li[@id='".md5('elen')."']/div[@class='item']/div[@class='item-meta']/span[@class='activity']/div[@class='member-offline']");
//--