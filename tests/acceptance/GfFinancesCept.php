<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1524, 1024);
$I->wantTo('see GF Financials');

// Financials works for the administrator
$I->amOnPage('/');
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','Founder_Counselor');
$I->fillField('pwd','#caRousal.72');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Financials', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Financials']");
$I->click('Accounting');
$I->seeCurrentUrlEquals('/browse/founder_counselor/gf-finances/');
$I->moveMouseOver("//div[@class='header-account-login']");
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
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','elen');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/elen/');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Financials', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Financials']");
$I->click('Accounting');
$I->seeCurrentUrlEquals('/browse/elen/gf-finances/');
$I->see('Congratulations! You are now at the highest level');
$I->wait(3);
$I->see('Sing-up Event');
$I->see('Sign-up Bonus');
$I->see('Talk Session - 30 min');
$I->click('#my-time-value');
$I->seeCurrentUrlEquals('/browse/elen/gf-finances/my-time-value/');
$I->see('Session Values not configured...');
$I->see('You currently do not have any talk session values defined. Therefore, you cannot start audio-video. But you can change this in the table below.');
$I->click('#levels');
$I->seeCurrentUrlEquals('/browse/elen/gf-finances/levels/');
$I->see('The Deep South');
$I->click('#user-invitations');
$I->seeCurrentUrlEquals('/browse/elen/invitations/');
$I->dontSee('Congratulations! You are now at the highest level');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Logout');
$I->click('Logout', '.logout');

// Financials dosn't work for the member
$I->amOnPage('/');
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->dontSee('Financials', 'li');
$I->amOnPage('/browse/saybb/gf-finances/');
$I->see("404");
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Logout');
$I->click('Logout', '.logout');

// Financials dosn't work for the pre-member
$I->amOnPage('/');
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','tanya');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/register-pre-member/');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->dontSee('Financials', 'li');
$I->amOnPage('/browse/tanya/gf-finances/');
$I->see("404");
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Logout');
$I->click('Logout', '.logout');