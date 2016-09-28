<?php
declare(strict_types = 1);
namespace Klapuch\Access;

use Klapuch\Storage;

/**
 * Verification code which always exists
 */
final class ExistingVerificationCode implements VerificationCode {
    private $origin;
    private $code;
    private $database;

    public function __construct(
        VerificationCode $origin,
        string $code,
        Storage\Database $database
    ) {
        $this->origin = $origin;
        $this->code = $code;
        $this->database = $database;
    }

    public function use () {
        if(!$this->exists($this->code)) {
            throw new \Exception(
                'The verification code does not exist'
            );
        }
        $this->origin->use();
    }

    public function owner(): User {
        if(!$this->exists($this->code)) {
            throw new \Exception(
                'Nobody owns the verification code'
            );
        }
        return $this->origin->owner();
    }

    /**
     * Does the verification code exist?
     * @param string $code
     * @return bool
     */
    private function exists(string $code): bool {
        return (bool)$this->database->fetchColumn(
            'SELECT 1
            FROM verification_codes
            WHERE code IS NOT DISTINCT FROM ?',
            [$code]
        );
    }
}
