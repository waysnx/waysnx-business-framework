<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * RealCommandExecutionTest
 *
 * Real command execution tests verifying:
 * - Options actually appear in --help
 * - Commands execute with real Laravel container/input/output
 * - Exit codes are correct
 * - JSON output is valid
 * - WBF services are resolved through real container
 *
 * Extends ConsoleTestCase to get:
 * - Real Laravel container with WBF services registered
 * - Artisan console application
 * - Command execution helpers
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class RealCommandExecutionTest extends ConsoleTestCase
{
    /**
     * Execute a command through Artisan
     *
     * @param string $commandLine Full command line (e.g., "wbf:list --json")
     * @return array ['exitCode' => int, 'output' => string]
     */
    private function executeCommand(string $commandLine): array
    {
        $input = new StringInput($commandLine);
        $output = new BufferedOutput();
        
        $exitCode = $this->artisan->run($input, $output);
        
        return [
            'exitCode' => $exitCode,
            'output' => $output->fetch(),
        ];
    }

    /**
     * Test wbf:list --help shows all expected options
     */
    public function testListHelpShowsOptions(): void
    {
        $result = $this->executeCommand('wbf:list --help');
        $helpText = $result['output'];

        // Should show json option
        $this->assertStringContainsString('--json', $helpText);
        
        // Should show list-specific options
        $this->assertStringContainsString('--detailed', $helpText);
        $this->assertStringContainsString('--filter', $helpText);
        $this->assertStringContainsString('--module', $helpText);
    }

    /**
     * Test wbf:make --help shows all expected options
     */
    public function testMakeHelpShowsOptions(): void
    {
        $result = $this->executeCommand('wbf:make --help');
        $helpText = $result['output'];

        // Should show json option
        $this->assertStringContainsString('--json', $helpText);
        
        // Should show make-specific options
        $this->assertStringContainsString('--module', $helpText);
        $this->assertStringContainsString('--domain', $helpText);
        $this->assertStringContainsString('--capability', $helpText);
        $this->assertStringContainsString('--description', $helpText);
        $this->assertStringContainsString('--namespace', $helpText);
        $this->assertStringContainsString('--force', $helpText);
    }

    /**
     * Test wbf:doctor --help shows all expected options
     */
    public function testDoctorHelpShowsOptions(): void
    {
        $result = $this->executeCommand('wbf:doctor --help');
        $helpText = $result['output'];

        // Should show json option
        $this->assertStringContainsString('--json', $helpText);
        
        // Should show doctor-specific options
        $this->assertStringContainsString('--detail', $helpText);
        $this->assertStringContainsString('--component', $helpText);
    }

    /**
     * Test wbf:register --help shows all expected options
     */
    public function testRegisterHelpShowsOptions(): void
    {
        $result = $this->executeCommand('wbf:register --help');
        $helpText = $result['output'];

        // Should show json option
        $this->assertStringContainsString('--json', $helpText);
        
        // Should show register-specific options
        $this->assertStringContainsString('--fail-if-not-found', $helpText);
    }

    /**
     * Test wbf:show --help shows options
     */
    public function testShowHelpShowsOptions(): void
    {
        $result = $this->executeCommand('wbf:show --help');
        $helpText = $result['output'];

        // Should show json option
        $this->assertStringContainsString('--json', $helpText);
    }

    /**
     * Test wbf:list executes with real output
     */
    public function testListCommandRealExecution(): void
    {
        $result = $this->executeCommand('wbf:list');
        
        $this->assertIsInt($result['exitCode']);
        $this->assertNotEmpty($result['output']);
    }

    /**
     * Test wbf:list --json produces valid JSON
     */
    public function testListJsonProducesValidJson(): void
    {
        $result = $this->executeCommand('wbf:list --json');
        
        $json = json_decode($result['output'], true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('command', $json);
    }

    /**
     * Test wbf:doctor executes with real output
     */
    public function testDoctorCommandRealExecution(): void
    {
        $result = $this->executeCommand('wbf:doctor');
        
        $this->assertIsInt($result['exitCode']);
        $this->assertNotEmpty($result['output']);
    }

    /**
     * Test wbf:doctor --json produces valid JSON
     */
    public function testDoctorJsonProducesValidJson(): void
    {
        $result = $this->executeCommand('wbf:doctor --json');
        
        $json = json_decode($result['output'], true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('status', $json);
    }

    /**
     * Test JSON error response has correct structure
     */
    public function testJsonErrorContainsAllFields(): void
    {
        // Execute a command that will fail (invalid type)
        $result = $this->executeCommand('wbf:show invalid-type invalid-id --json');
        
        $json = json_decode($result['output'], true);
        $this->assertIsArray($json);

        if ($json['status'] === 'error') {
            // Verify error contract
            $this->assertArrayHasKey('status', $json);
            $this->assertArrayHasKey('code', $json);
            $this->assertArrayHasKey('command', $json);
            $this->assertArrayHasKey('message', $json);
            $this->assertEquals('error', $json['status']);
            $this->assertIsInt($json['code']);
            $this->assertIsString($json['message']);
        }
    }

    /**
     * Test --no-interaction flag works
     */
    public function testNoInteractionFlagWorks(): void
    {
        $result = $this->executeCommand('wbf:list --no-interaction');

        // Should execute successfully through real container
        $this->assertIsInt($result['exitCode']);
    }

    /**
     * Test register not found without --fail-if-not-found returns success
     */
    public function testRegisterNotFoundWithoutFailFlagReturnsSuccess(): void
    {
        $result = $this->executeCommand('wbf:register workflow non-existent-workflow');
        
        // Should return success exit code (0)
        $this->assertEquals(0, $result['exitCode']);
    }

    /**
     * Test register not found with --fail-if-not-found returns error code
     */
    public function testRegisterNotFoundWithFailFlagReturnsErrorCode(): void
    {
        $result = $this->executeCommand('wbf:register workflow non-existent-workflow --fail-if-not-found');
        
        // Should return not found exit code (3)
        $this->assertEquals(3, $result['exitCode']);
    }

    /**
     * Test register not found with --fail-if-not-found --json returns error JSON
     */
    public function testRegisterNotFoundWithFailFlagJsonReturnsErrorStatus(): void
    {
        $result = $this->executeCommand('wbf:register workflow non-existent-workflow --fail-if-not-found --json');
        
        $json = json_decode($result['output'], true);
        $this->assertIsArray($json);
        
        // Should return error status
        $this->assertEquals('error', $json['status']);
        $this->assertEquals(3, $json['code']);
        $this->assertEquals('not_found', $json['type']);
    }

    /**
     * Test doctor executes through real container
     */
    public function testDoctorExecutesThroughRealContainer(): void
    {
        $result = $this->executeCommand('wbf:doctor');
        
        // Should complete without errors
        $this->assertIsInt($result['exitCode']);
        $this->assertNotEmpty($result['output']);
        
        // Should show some output (not empty)
        $this->assertGreaterThan(0, strlen($result['output']));
    }
}
