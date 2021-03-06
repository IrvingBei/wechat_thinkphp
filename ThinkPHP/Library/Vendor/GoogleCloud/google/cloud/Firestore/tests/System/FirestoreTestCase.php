<?php
/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Firestore\Tests\System;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Testing\System\SystemTestCase;

class FirestoreTestCase extends SystemTestCase
{
    const COLLECTION = 'system-test';

    protected static $client;
    protected static $collection;
    private static $hasSetUp = false;

    public static function setupBeforeClass()
    {
        if (self::$hasSetUp) {
            return;
        }

        $keyFilePath = getenv('GOOGLE_CLOUD_PHP_FIRESTORE_TESTS_KEY_PATH');
        self::$client = new FirestoreClient([
            'keyFilePath' => $keyFilePath
        ]);
        self::$collection = self::$client->collection(self::COLLECTION);

        self::$hasSetUp = true;
    }
}
