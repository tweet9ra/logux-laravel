<?php


namespace tweet9ra\Logux\Laravel;

use Illuminate\Foundation\Testing\TestResponse;
use \Illuminate\Foundation\Testing\Assert as PHPUnit;

/**
 * Logux response decorator for TestResponse
 * @mixin TestResponse
 */
class LoguxTestResponse
{
    /** @var TestResponse */
    protected $testResponse;

    public function __construct(TestResponse $testResponse)
    {
        $this->testResponse = $testResponse;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->testResponse, $method), $args);
    }

    public function __get($property)
    {
        return $this->testResponse->$property;
    }

    public function __set($property, $value)
    {
        $this->testResponse->$property = $value;
        return $this;
    }

    /**
     * Check that executed action was approved & processed
     */
    public function assertActionProcessedAndApproved()
    {
        $this->assertJson([['answer' => 'approved'], ['answer' => 'processed']]);
    }

    /**
     * Check that executed action has errors
     * @param string|null $errorMessage If you want to check error strings
     */
    public function assertActionHasError(string $errorMessage = null)
    {
        if ($errorMessage) {
            $this->assertJson([['answer' => 'error', 'details' => $errorMessage]]);
        } else {
            $this->assertJson([['answer' => 'error']]);
        }
    }

    public function assertActionWasResendedTo(array $recipients)
    {
        $resends = [];
        foreach ($this->json() as $actions) {
            if ($actions['answer'] === 'resend') {
                $resends[] = $actions['channels'];
            } else {
                continue;
            }

            if ($actions['channels'] === $recipients) {
                return $this;
            }
        }

        $strResends = implode("\n", array_map(function ($el) {
            return print_r($el, true);
        }, $resends));

        PHPUnit::fail("\nUnable to find resended action to recipients: \n".print_r($recipients, true)
            . "\nFounded resends: \n" . $strResends."\n");

        return $this;
    }
}