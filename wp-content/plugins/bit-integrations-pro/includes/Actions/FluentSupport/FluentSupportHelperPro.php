<?php

/**
 * FluentSupport    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\FluentSupport;

use BitCode\FI\Core\Util\Common;

/**
 * Provide functionality for Record insert, upsert
 */
class FluentSupportHelperPro
{
    public static function uploadTicketAttachments($finalData, $attachments, $ticket, $customer, $flowId)
    {
        if (empty($attachments)) {
            return;
        }

        if (!class_exists(\FluentSupport\App\Services\Tickets\TicketService::class)
            || !class_exists(\FluentSupport\App\Models\Attachment::class)) {
            return;
        }

        $finalData['attachments'] = static::uploadTicketFiles($attachments, $finalData['customer_id'], $flowId);

        \FluentSupport\App\Services\Tickets\TicketService::addTicketAttachments($finalData, [], $ticket, $customer);
    }

    private static function uploadTicketFiles($files, $customerId, $flowId)
    {
        $attachments = [];
        $files = static::prepareAttachments($files, $flowId);

        foreach ($files as $file) {
            if (empty($file['file_path'])) {
                continue;
            }

            $fileData = [
                'ticket_id' => null,
                'person_id' => (int) $customerId,
                'file_type' => $file['type'],
                'file_path' => $file['file_path'],
                'full_url'  => esc_url($file['url']),
                'title'     => sanitize_file_name($file['name']),
                'driver'    => 'local',
                'status'    => 'in-active',
                'settings'  => [
                    'local_temp_path' => $file['file_path'],
                ]
            ];

            try {
                $attachment = \FluentSupport\App\Models\Attachment::create($fileData);
                $attachments[] = $attachment->file_hash;
            } catch (Exception $exception) {
                error_log($exception->getMessage());

                continue;
            }
        }

        return $attachments;
    }

    private static function prepareAttachments($files, $flowId)
    {
        $attachments = [];

        foreach ((array) $files as $file) {
            if (\is_array($file)) {
                $attachments = array_merge($attachments, static::prepareAttachments($file, $flowId));
            } else {
                $path = Common::filePath($file);
                $attachments[] = [
                    'file_path' => $path,
                    'url'       => Common::fileUrl($path),
                    'name'      => basename($path),
                    'type'      => mime_content_type($path),
                    'size'      => filesize($path),
                ];
            }
        }

        return $attachments;
    }
}
