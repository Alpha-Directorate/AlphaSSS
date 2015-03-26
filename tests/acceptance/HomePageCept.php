<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Verify that home page is works');
$I->amOnPage('/');
$I->see('Where has our privacy online gone?','h1');
$I->seeCurrentUrlEquals('/');
$I->see('Home','li');
$I->click('Home','li');
$I->seeCurrentUrlEquals('/');
$I->see('Where has our privacy online gone?','h1');
$I->click('Alpha Social Club','.site-title a');
$I->seeCurrentUrlEquals('/');
$I->see('Where has our privacy online gone?','h1');
