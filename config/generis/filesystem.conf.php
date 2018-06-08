<?php
/**
 * Default config header created during install
 */

return new oat\oatbox\filesystem\FileSystemService(array(
    'filesPath' => FILES_PATH,
    'adapters' => array(
        'itemDirectory' => array(
            'class' => 'oat\\remoteFlysystem\\models\\PDOLocalReplicateAdapter',
            'options' => [
                array(
                    'local_root' => FILES_PATH.'taoItems/itemData',
                    'pdo_prefix' => 'taoItems'
                )
            ]
        ),
        'taoQtiItem' => array(
            'class' => 'oat\\remoteFlysystem\\models\\PDOLocalReplicateAdapter',
            'options' => [
                array(
                    'local_root' => FILES_PATH.'taoQtiItem',
                    'pdo_prefix' => 'taoQtiItem'
                )
            ]
        ),
        'taoQtiTest' => array(
            'class' => 'oat\\remoteFlysystem\\models\\PDOLocalReplicateAdapter',
            'options' => [
                array(
                    'local_root' => FILES_PATH.'taoQtiTest',
                    'pdo_prefix' => 'taoQtiTest'
                )
            ]
        ),
        'taskQueueStorage' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'taskQueueStorage'
            )
        ),
        'fileUploadDirectory' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'tao/upload'
            )
        ),
        'public' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'tao/public'
            )
        ),
        'private' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'tao/private'
            )
        ),
        'log' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'tao/log'
            )
        ),
        'sharedTmp' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'tmp'
            )
        ),
        'stateBackup' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'stateBackup'
            )
        ),
        'taoQtiTestSessionFilesystem' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'taoQtiTestSessionFilesystem'
            )
        ),
        'qtiItemPci' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'qtiItemPci'
            )
        ),
        'qtiItemImsPci' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'qtiItemImsPci'
            )
        ),
        'portableElementStorage' => array(
            'class' => 'Local',
            'options' => array(
                'root' => FILES_PATH.'portableElement'
            )
        )
    )
));
