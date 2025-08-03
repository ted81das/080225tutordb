<?php

namespace BitApps\BTCBI_PRO\Triggers\Voxel;

class VoxelTasks
{
    public const COLLECTION_NEW_POST_CREATED = 'voxel-1';

    public const COLLECTIONS_POST_UPDATED = 'voxel-2';

    public const NEW_PROFILE_CREATED = 'voxel-3';

    public const PROFILE_UPDATED = 'voxel-4';

    public const PROFILE_APPROVED = 'voxel-5';

    public const PROFILE_REJECTED = 'voxel-6';

    public const POST_SUBMITTED = 'voxel-7';

    public const POST_UPDATED = 'voxel-8';

    public const POST_APPROVED = 'voxel-9';

    public const POST_REJECTED = 'voxel-10';

    public const POST_REVIEWED = 'voxel-11';

    public const USER_RECEIVES_MESSAGE = 'voxel-12';

    public const USER_REGISTERED_FOR_MEMBERSHIP = 'voxel-13';

    public const MEMBERSHIP_PLAN_ACTIVATED = 'voxel-14';

    public const MEMBERSHIP_PLAN_SWITCHED = 'voxel-15';

    public const MEMBERSHIP_PLAN_CANCELED = 'voxel-16';

    public const NEW_COMMENT_ON_TIMELINE = 'voxel-17';

    public const COMMENT_NEW_REPLY = 'voxel-18';

    public const PROFILE_NEW_WALL_POST = 'voxel-19';

    public const NEW_WALL_POST_BY_USER = 'voxel-20';

    public const NEW_ORDER_PLACED = 'voxel-21';

    public const ORDER_APPROVED_BY_VENDOR = 'voxel-22';

    public const ORDER_DECLINED_BY_VENDOR = 'voxel-23';

    public const ORDER_CANCELED_BY_CUSTOMER = 'voxel-24';

    public const ORDERS_CLAIM_LISTING = 'voxel-25';

    public const ORDER_PROMOTION_ACTIVATED = 'voxel-26';

    public const ORDER_PROMOTION_CANCELED = 'voxel-27';

    public static function getTaskList()
    {
        return [
            new Task(VoxelTasks::COLLECTION_NEW_POST_CREATED, __('Collection New Post Created', 'bit-integrations-pro'), __('Runs after a new collection is created.', 'bit-integrations-pro')),
            new Task(VoxelTasks::COLLECTIONS_POST_UPDATED, __('Collection Post Updated', 'bit-integrations-pro'), __('Runs after an existing collection is updated.', 'bit-integrations-pro')),
            new Task(VoxelTasks::NEW_PROFILE_CREATED, __('New Profile Created', 'bit-integrations-pro'), __('Runs after a new profile is created.', 'bit-integrations-pro')),
            new Task(VoxelTasks::PROFILE_UPDATED, __('Profile Updated', 'bit-integrations-pro'), __('Runs after a profile is updated.', 'bit-integrations-pro')),
            new Task(VoxelTasks::PROFILE_APPROVED, __('Profile Approved', 'bit-integrations-pro'), __('Runs after a profile is approved.', 'bit-integrations-pro')),
            new Task(VoxelTasks::PROFILE_REJECTED, __('Profile Rejected', 'bit-integrations-pro'), __('Runs after a profile is rejected.', 'bit-integrations-pro')),
            new Task(VoxelTasks::POST_SUBMITTED, __('Post Submitted', 'bit-integrations-pro'), __('Runs after a specific post type\'s post is submitted.', 'bit-integrations-pro')),
            new Task(VoxelTasks::POST_UPDATED, __('Post Updated', 'bit-integrations-pro'), __('Runs after a specific post type\'s post is updated.', 'bit-integrations-pro')),
            new Task(VoxelTasks::POST_APPROVED, __('Post Approved', 'bit-integrations-pro'), __('Runs after a specific post type\'s post is approved.', 'bit-integrations-pro')),
            new Task(VoxelTasks::POST_REJECTED, __('Post Rejected', 'bit-integrations-pro'), __('Runs after a specific post type\'s post is rejected.', 'bit-integrations-pro')),
            new Task(VoxelTasks::POST_REVIEWED, __('Post Reviewed', 'bit-integrations-pro'), __('Runs after a specific post type\'s post is reviewed.', 'bit-integrations-pro')),
            new Task(VoxelTasks::USER_RECEIVES_MESSAGE, __('User Receives Message', 'bit-integrations-pro'), __('Runs after a user receives a message.', 'bit-integrations-pro')),
            new Task(VoxelTasks::USER_REGISTERED_FOR_MEMBERSHIP, __('User Registered for Membership', 'bit-integrations-pro'), __('Runs after a new user is registered for membership.', 'bit-integrations-pro')),
            new Task(VoxelTasks::MEMBERSHIP_PLAN_ACTIVATED, __('Membership Plan Activated', 'bit-integrations-pro'), __('Runs after a membership plan is activated.', 'bit-integrations-pro')),
            new Task(VoxelTasks::MEMBERSHIP_PLAN_SWITCHED, __('Membership Plan Switched', 'bit-integrations-pro'), __('Runs after a membership plan is switched.', 'bit-integrations-pro')),
            new Task(VoxelTasks::MEMBERSHIP_PLAN_CANCELED, __('Membership Plan Canceled', 'bit-integrations-pro'), __('Runs after a membership plan is canceled.', 'bit-integrations-pro')),
            new Task(VoxelTasks::NEW_COMMENT_ON_TIMELINE, __('New Comment', 'bit-integrations-pro'), __('Runs after a new comment is added on the timeline.', 'bit-integrations-pro')),
            new Task(VoxelTasks::COMMENT_NEW_REPLY, __('Comment New Reply', 'bit-integrations-pro'), __('Runs after a new comment reply is created on the timeline.', 'bit-integrations-pro')),
            new Task(VoxelTasks::PROFILE_NEW_WALL_POST, __('Profile New Wall Post', 'bit-integrations-pro'), __('Runs after a user adds a post to their wall.', 'bit-integrations-pro')),
            new Task(VoxelTasks::NEW_WALL_POST_BY_USER, __('New Wall Post by User', 'bit-integrations-pro'), __('Runs after a new post is created on specified post type wall.', 'bit-integrations-pro')),
            new Task(VoxelTasks::NEW_ORDER_PLACED, __('New Order Placed', 'bit-integrations-pro'), __('Runs after a new order is placed by customer.', 'bit-integrations-pro')),
            new Task(VoxelTasks::ORDER_APPROVED_BY_VENDOR, __('Order Approved by Vendor', 'bit-integrations-pro'), __('Runs after an order is approved by vendor.', 'bit-integrations-pro')),
            new Task(VoxelTasks::ORDER_DECLINED_BY_VENDOR, __('Order Declined by Vendor', 'bit-integrations-pro'), __('Runs after an order is declined by vendor.', 'bit-integrations-pro')),
            new Task(VoxelTasks::ORDER_CANCELED_BY_CUSTOMER, __('Order Canceled by Customer', 'bit-integrations-pro'), __('Runs after a order is canceled by customer.', 'bit-integrations-pro')),
            new Task(VoxelTasks::ORDERS_CLAIM_LISTING, __('Orders Claim Listing', 'bit-integrations-pro'), __('Runs after a order listing is claimed.', 'bit-integrations-pro')),
            new Task(VoxelTasks::ORDER_PROMOTION_ACTIVATED, __('Order Promotion Activated', 'bit-integrations-pro'), __('Runs after a promotion is activated on an order.', 'bit-integrations-pro')),
            new Task(VoxelTasks::ORDER_PROMOTION_CANCELED, __('Order Promotion Cancelled', 'bit-integrations-pro'), __('Runs after a promotion is canceled on an order.', 'bit-integrations-pro')),
        ];
    }

    public static function isPostTypeTask($task)
    {
        return \in_array($task, [
            VoxelTasks::POST_SUBMITTED,
            VoxelTasks::POST_UPDATED,
            VoxelTasks::POST_APPROVED,
            VoxelTasks::POST_REJECTED,
            VoxelTasks::POST_REVIEWED,
            VoxelTasks::NEW_WALL_POST_BY_USER
        ]);
    }
}

class Task
{
    public string $id;

    public string $title;

    public string $note;

    public function __construct(string $id, string $title, string $note)
    {
        $this->id = $id;
        $this->title = $title;
        $this->note = $note;
    }
}
