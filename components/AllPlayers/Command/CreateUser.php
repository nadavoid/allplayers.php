<?php

namespace AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends a simple API request to an example web service
 *
 * @guzzle firstname doc="User first name." required="true"
 * @guzzle lastname doc="User last name." required="true"
 * @guzzle email doc="Valid User's Email Address." required="true"
 * @guzzle gender doc="User gender (m or f)." required="true"
 * @guzzle birthday doc="Birthday passed in the form of YYYY-MM-DD." required="true"
 * @guzzle password doc="Password for the user; if not passed: automatically generated."
 * @guzzle external_id doc="External ID up to 72 characters to be used for relating to external content."
 * @guzzle notification doc="Message to be appended to the notification email sent to the user."
 */
class CreateUser extends AbstractCommand
{
    /**
     * Set the create user params.
     *
     * @param array $params
     *  Params array containing all required fields.
     *
     * @return All params array.
     */
    public function setParams($params)
    {
        foreach ($params as $key => $value) {
            $this->setParams($params);
        }

        return $this->getAll($params);
    }

    protected function build()
    {
        $this->request = $this->client->post('users.json', NULL, $this->data);
    }
}
