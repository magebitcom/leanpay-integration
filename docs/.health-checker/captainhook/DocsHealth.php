<?php
/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */

declare(strict_types=1);

namespace Magebit\Hooks\Docs;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hook\Action;
use CaptainHook\App\Hooks;
use SebastianFeldmann\Cli\Command\Executable;
use SebastianFeldmann\Cli\Processor\ProcOpen;
use SebastianFeldmann\Git\Repository;

/**
 * CaptainHook commit-msg action that runs docs/.health-checker/run on the message being committed.
 *
 * CaptainHook prints a successful CLI action's output only in verbose mode, which hides the warnings
 * the report carries. This action prints the report whenever there is one, and fails the commit when
 * the checker exits 1 (an error) or 2 (it could not run). Other hook managers call the run script directly.
 * The command is built and run with CaptainHook's own command classes, so the project's linters see no shell
 * function here. The run script changes to the repository root itself.
 */
class DocsHealth implements Action
{
    private const RUN_SCRIPT = 'docs/.health-checker/run';

    /**
     * Runs the docs check on the message being committed.
     *
     * @param Config $config
     * @param IO $io
     * @param Repository $repository
     * @param Config\Action $action
     * @return void
     * @throws ActionFailed
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $command = (new Executable('sh'))
            ->addArgument($repository->getRoot() . '/' . self::RUN_SCRIPT)
            ->addArgument($io->getArgument(Hooks::ARG_MESSAGE_FILE, '.git/COMMIT_EDITMSG'))
            ->getCommand() . ' 2>&1';
        $result = (new ProcOpen())->run($command);
        $output = trim($result->getStdOut());

        if (!$result->isSuccessful()) {
            throw new ActionFailed($output !== '' ? $output : 'docs-health failed: ' . $command);
        }

        if ($output !== '') {
            $io->write($output);
        }
    }
}
