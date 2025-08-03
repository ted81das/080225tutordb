<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Voxel\VoxelHelper;
use BitApps\BTCBI_PRO\Triggers\Voxel\VoxelController;

Hooks::add('voxel/app-events/post-types/collection/post:submitted', [VoxelController::class, 'handleCollectionNewPost'], 10, 1);
Hooks::add('voxel/app-events/post-types/collection/post:updated', [VoxelController::class, 'handleCollectionPostUpdated'], 10, 1);
Hooks::add('voxel/app-events/post-types/profile/post:submitted', [VoxelController::class, 'handleNewProfileCreated'], 10, 1);
Hooks::add('voxel/app-events/post-types/profile/post:updated', [VoxelController::class, 'handleProfileUpdated'], 10, 1);
Hooks::add('voxel/app-events/post-types/profile/post:approved', [VoxelController::class, 'handleProfileApproved'], 10, 1);
Hooks::add('voxel/app-events/post-types/profile/post:rejected', [VoxelController::class, 'handleProfileRejected'], 10, 1);
Hooks::add('voxel/app-events/messages/user:received_message', [VoxelController::class, 'handleMessageReceived'], 10, 1);
Hooks::add('voxel/app-events/membership/user:registered', [VoxelController::class, 'handleMembershipUserRegister'], 10, 1);
Hooks::add('voxel/app-events/membership/plan:activated', [VoxelController::class, 'handleMembershipPlanActivated'], 10, 1);
Hooks::add('voxel/app-events/membership/plan:switched', [VoxelController::class, 'handleMembershipPlanSwitched'], 10, 1);
Hooks::add('voxel/app-events/membership/plan:canceled', [VoxelController::class, 'handleMembershipPlanCanceled'], 10, 1);
Hooks::add('voxel/app-events/timeline/comment:created', [VoxelController::class, 'handleNewComment'], 10, 1);
Hooks::add('voxel/app-events/timeline/comment-reply:created', [VoxelController::class, 'handleCommentReply'], 10, 1);
Hooks::add('voxel/app-events/post-types/profile/wall-post:created', [VoxelController::class, 'handleProfileNewWallPost'], 10, 1);
Hooks::add('voxel/app-events/products/orders/customer:order_placed', [VoxelController::class, 'handleNewOrder'], 10, 1);
Hooks::add('voxel/app-events/products/orders/vendor:order_approved', [VoxelController::class, 'handleOrderApproved'], 10, 1);
Hooks::add('voxel/app-events/products/orders/vendor:order_declined', [VoxelController::class, 'handleOrderDeclined'], 10, 1);
Hooks::add('voxel/app-events/products/orders/customer:order_canceled', [VoxelController::class, 'handleOrderCancel'], 10, 1);
Hooks::add('voxel/app-events/claims/claim:processed', [VoxelController::class, 'handleOrderClaimListing'], 10, 1);
Hooks::add('voxel/app-events/promotions/promotion:activated', [VoxelController::class, 'handlePromotionActivated'], 10, 1);
Hooks::add('voxel/app-events/promotions/promotion:canceled', [VoxelController::class, 'handlePromotionCanceled'], 10, 1);

$voxelPostTypes = VoxelHelper::getPostTypeRaw();

foreach ($voxelPostTypes as $type) {
    Hooks::add('voxel/app-events/post-types/' . $type . '/post:submitted', [VoxelController::class, 'handlePostSubmitted'], 10, 1);
    Hooks::add('voxel/app-events/post-types/' . $type . '/post:updated', [VoxelController::class, 'handlePostUpdated'], 10, 1);
    Hooks::add('voxel/app-events/post-types/' . $type . '/post:approved', [VoxelController::class, 'handlePostApproved'], 10, 1);
    Hooks::add('voxel/app-events/post-types/' . $type . '/post:rejected', [VoxelController::class, 'handlePostRejected'], 10, 1);
    Hooks::add('voxel/app-events/post-types/' . $type . '/review:created', [VoxelController::class, 'handlePostReviewed'], 10, 1);
    Hooks::add('voxel/app-events/post-types/' . $type . '/wall-post:created', [VoxelController::class, 'handleNewWallPost'], 10, 1);
}
