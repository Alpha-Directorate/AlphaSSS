<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('see GF Financials');

// Financials works for the administrator
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','Founder_Counselor');
$I->fillField('pwd','#caRousal.72');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Financials', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Financials']");
$I->click('Accounting');
$I->seeCurrentUrlEquals('/browse/founder_counselor/gf-finances/');
$I->moveMouseOver('#wp-admin-bar-site-name');
$I->click('Dashboard');
$I->moveMouseOver('.dashicons-admin-users');
$I->click('All Users');
$I->click('elen');
$I->selectOption('#role', 'Girlfriend');
$I->click('#submit');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");

// Test girlfriend actions
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','elen');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/elen/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Financials', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Financials']");
$I->click('Accounting');
$I->seeCurrentUrlEquals('/browse/elen/gf-finances/');
$I->see('Congratulations! You are now at the highest level');
$I->wait(3);
$I->see('Sing-up Event');
$I->see('Sign-up Bonus');
$I->see('Talk Session - 30 min');
$I->click('#levels');
$I->seeCurrentUrlEquals('/browse/elen/gf-finances/levels/');
$I->see('The Deep South');
$I->click('#user-invitations');
$I->seeCurrentUrlEquals('/browse/elen/invitations/');
$I->dontSee('Congratulations! You are now at the highest level');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");

// Financials dosn't work for the member
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->dontSee('Financials', 'li');
$I->amOnPage('/browse/saybb/gf-finances/');
$I->see("Not Found");
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");

// Financials dosn't work for the pre-member
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','tanya');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/register-pre-member/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->dontSee('Financials', 'li');
$I->amOnPage('/browse/tanya/gf-finances/');
$I->see("Not Found");
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");