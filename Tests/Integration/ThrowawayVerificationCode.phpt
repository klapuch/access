<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Access\Integration;

use Klapuch\Access;
use Klapuch\Access\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class ThrowawayVerificationCode extends TestCase\Database {
	public function testUsingValidCode() {
		$this->prepareValidCode();
		(new Access\ThrowawayVerificationCode(
			'valid:code',
			$this->database
		))->use();
		$statement = $this->database->prepare(
			"SELECT used
			FROM verification_codes
			WHERE code = 'valid:code'"
		);
		$statement->execute();
		Assert::true($statement->fetchColumn());
	}

	/**
	 * @throws \Exception Verification code was already used
	 */
	public function testThrowinOnUsingAlreadyActivatedCode() {
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used, used_at) VALUES
			(2, 'activated:code', TRUE, NOW())"
		);
		(new Access\ThrowawayVerificationCode(
			'activated:code',
			$this->database
		))->use();
	}

	private function prepareValidCode() {
		$this->database->exec(
			"INSERT INTO users (email, password, role) VALUES
			('foo@gmail.com', 'password', 'member'),
			('ber@gmail.com', 'password', 'member')"
		);
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used)
			VALUES (1, 'valid:code', FALSE)"
		);
	}

	protected function prepareDatabase(): void {
		$this->purge(['verification_codes', 'users']);
	}
}

(new ThrowawayVerificationCode())->run();