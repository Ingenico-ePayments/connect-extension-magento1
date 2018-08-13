<?php

/**
 * Class Netresearch_Epayments_Model_TokenService
 */
class Netresearch_Epayments_Model_TokenService
{
    /**
     * @var Netresearch_Epayments_Model_Token[]
     */
    protected $tokens = array();

    /**
     * @var Netresearch_Epayments_Model_Resource_Token_Collection
     */
    protected $tokenCollection;

    /**
     * Netresearch_Epayments_Model_TokenService constructor.
     */
    public function __construct()
    {
        $this->tokenCollection = Mage::getModel('netresearch_epayments/token')->getCollection();
    }


    /**
     * Assign token to customerId
     *
     * @param int $customerId
     * @param string $tokenString
     * @return $this
     * @throws Exception
     */
    public function assignToken($customerId, $tokenString)
    {
        // Only registered customers can have tokens
        if ($customerId && (string) $tokenString) {
            // Disallow duplicate tokens
            if ($this->tokenExists($customerId, $tokenString)) {
                return $this;
            }
            $token = Mage::getModel('netresearch_epayments/token');
            $token->setCustomerId($customerId);
            $token->setTokenString($tokenString);
            $token->save();

            $this->tokens[] = $token;
        }

        return $this;
    }

    /**
     * Check if a token is already assigned
     *
     * @param $tokenString
     * @return bool
     */
    protected function tokenExists($customerId, $tokenString)
    {
        foreach ($this->tokenCollection->addCustomerIdFilter($customerId)->getItems() as $token) {
            if ($token->getTokenString() === $tokenString) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find all tokens for customer
     *
     * @param int|null $customerId
     * @return Netresearch_Epayments_Model_Token[]
     */
    public function find($customerId = null)
    {
        if ($customerId) {
            $this->tokens = $this->tokenCollection->addCustomerIdFilter($customerId)->getItems();
        }

        return $this->tokens;
    }

    /**
     * Delete all given tokens
     *
     * @param string[] $tokens - tokens to delete
     * @param int|null $customerId - additional filter for customerId
     */
    public function deleteAll($tokens, $customerId = null)
    {
        if ($customerId) {
            $this->tokenCollection->addCustomerIdFilter($customerId);
        }

        $this->tokenCollection->addFieldToFilter(
            'token_string',
            array(
                'in',
                $tokens
            )
        );

        foreach ($this->tokenCollection->getItems() as $token) {
            $token->isDeleted(true);
        }
        $this->tokenCollection->save();
    }
}
