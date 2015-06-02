<?php
/**
 * Class DmConfirmEmail_Models_Registration
 *
 * Handles new registration
 */
class DmConfirmEmail_Models_Member {

	public function __construct()
	{
		add_action('pre_user_email', function($email){
			return $email;
		});

		add_action('member_registered', function(){

			// Get user info
			$user = get_userdata(get_current_user_id());

			// Parse the messages
			$parsedSubject = DmConfirmEmail::parser(__('Switzerland is small and neutral! We are more like Germany, ambitious and misunderstood!', 'alphasss'), '', $user->user_login);
			$parsedMessage = DmConfirmEmail::parser(__("Too much work. Let's burn it and say we dumped it in the sewer. Oh, you're a dollar naughtier than most. When I was first asked to make a film about my nephew, Hubert Farnsworth, I thought \"Why should I?\" Then later, Leela made the film. But if I did make it, you can bet there would have been more topless women on motorcycles. Roll film! What are their names? Too much work. Let's burn it and say we dumped it in the sewer. You can see how I lived before I met you.
                Ah, yes! John Quincy Adding Machine. He struck a chord with the voters when he pledged not to go on a killing spree. Is today's hectic lifestyle making you tense and impatient? Oh, how I wish I could believe or understand that! There's only one reasonable course of action now: kill Flexo! I don't want to be rescued.", 'alphasss'), '', $user->user_login);
			// Clean the message
			$subject = html_entity_decode($parsedSubject);
			$message = html_entity_decode($parsedMessage);
			$email   = ( new AlphaSSS\Helpers\Encryption )->decode( str_replace('@alphasss.com', '', $user->user_email) );

			// Send email
			$send = wp_mail( $email, $subject, $message );
		});
	}
}