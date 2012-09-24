<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends an API request to create a user.
 *
 * @guzzle firstname    type="string" doc="User first name." required="true"
 * @guzzle lastname     type="string" doc="User last name." required="true"
 * @guzzle email        type="string" doc="Valid User's Email Address." required="true"
 * @guzzle gender       type="string" doc="User gender (m or f)." required="true"
 * @guzzle birthday     type="date" doc="Birthday passed in the form of YYYY-MM-DD." required="true"
 * @guzzle password     type="string" doc="Password for the user; if not passed: automatically generated."
 * @guzzle external_id  type="string" doc="External ID up to 72 characters to be used for relating to external content."
 * @guzzle notification type="string" doc="Message to be appended to the notification email sent to the user."
 */
class CreateUser extends AbstractCommand
{
    protected function build()
    {
        $params = $this->getAll(array(
            'firstname', 'lastname', 'email', 'gender', 'birthday', 'password', 'external_id', 'notification'));
        $this->request = $this->client->post('users', NULL, $params);
    }
}
