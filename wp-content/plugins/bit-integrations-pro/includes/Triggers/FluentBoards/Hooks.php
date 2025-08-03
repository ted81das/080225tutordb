<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FluentBoards\FluentBoardsController;

Hooks::add('fluent_boards/board_created', [FluentBoardsController::class, 'handleNewBoardCreated'], 10, 1);
Hooks::add('fluent_boards/board_member_added', [FluentBoardsController::class, 'handleBoardMemberAdded'], 10, 2);
Hooks::add('fluent_boards/task_created', [FluentBoardsController::class, 'handleNewTaskCreated'], 10, 1);
