<?php
/**
 * Created by PhpStorm.
 * User: SHARRISON
 * Date: 12/11/13
 * Time: 8:50 AM
 */
$actionMessage = $_['actionmsg'];
$errorMessage = $_['error'];
$txList = $_['txLog'];
$txReview = $_['txReview'];
$txReceive = $_['txReceive'];
$alt = false;
//return $this->render('main', array(
//    'error' => $errorMessage,
//    'txLog' => $transmissionLog,
//    'txReview' => $transmissionsToReview,
//    'txReceive' => $transmissionsToReceive,
//));

function constructUrl($dlPath)
{
    $portBit = '';
    $currentUrl = 'http';
    if ($_SERVER['HTTPS'] == 'on') {
        $currentUrl .= 's';
        if ($_SERVER['SERVER_PORT'] != '443') {
            $portBit = ':' . $_SERVER['SERVER_PORT'];
        }
    } else {
        if ($_SERVER['SERVER_PORT'] != '80') {
            $portBit = ':' . $_SERVER['SERVER_PORT'];
        }
    }
    $currentUrl = $currentUrl . '://' . $_SERVER['SERVER_NAME'] . $portBit . '/index.php/apps/files/download/Shared/' . $dlPath;
    return $currentUrl;
}

?>
<div class="transmissionLog">
    <?php if (!empty($actionMessage)) { ?>
        <p><?php p($actionMessage); ?></p>
        <br/>
    <?php } ?>
    <?php
    if (!empty($errorMessage)) {
        ?>
        <p><?php p($errorMessage); ?></p>
    <?php } else if (!is_null($txList) && count($txList) > 0) { ?>

        <h1>Transmission Log</h1>
        <p>The table below lists the <b><?php p(count($txList)); ?></b> transmissions sent from this Financial
            Institution.</p>

        <table class="txTable">
            <tr>
                <th>Transmission ID</th>
                <th>Sender's File ID</th>
                <th>Sending Time</th>
                <th>Recipient</th>
                <th>Release Authority</th>
                <th>File Size</th>
                <th>State</th>
                <th>TToD</th>
            </tr>
            <?php foreach ($txList as $tx) { ?>
                <tr class="<?php if ($alt) {
                    p('alt');
                }
                $alt = !$alt; ?>">
                    <td><?php p($tx->getId()); ?></td>
                    <td><?php p($tx->getFileName()); ?></td>
                    <td><?php p($tx->getFileTime()); ?></td>
                    <td><?php p($tx->getRecipient()); ?></td>
                    <td><?php p($tx->getIntermediate()); ?></td>
                    <td><?php p($tx->getFileSize()); ?></td>
                    <td><?php p($tx->getStatelabel()); ?></td>
                    <td>03:00:10</td>
                </tr>
            <?php } ?>
        </table>

    <?php } else if ((!is_null($txReview) && count($txReview) > 0) || (!is_null($txReceive) && count($txReceive) > 0)) { ?>
        <?php if (count($txReview) > 0) { ?>
            <h1>Transmissions to Review</h1>
            <p>The table below lists <b><?php count($txReview); ?></b> incoming transmissions to review for release to
                their final Recipient.</p>

            <table class="txTable">
                <tr>
                    <th>Transmission ID</th>
                    <th>Sending Time</th>
                    <th>Sender</th>
                    <th>File Size</th>
                    <th>Recipient Org</th>
                    <th>State</th>
                    <th>Download</th>
                    <th>Dispense</th>
                    <th>TToE</th>
                </tr>
                <?php foreach ($txReview as $tx) { ?>
                    <tr class="<?php if ($alt) {
                        p('alt');
                    }
                    $alt = !$alt; ?>">
                        <td><?php p($tx->getId()); ?></td>
                        <td><?php p($tx->getFileTime()); ?></td>
                        <td><?php p($tx->getSender()); ?></td>
                        <td><?php p($tx->getFileSize()); ?></td>
                        <td><?php p($tx->getRecipient()); ?></td>
                        <td><?php p($tx->getStatelabel()); ?></td>
                        <td>
                            <?php
                            // only construct/display download link when file is still available.
                            if ($tx->getState() != \OCA\FileRouter\TransmissionStates::Deleted && $tx->getState() != \OCA\FileRouter\TransmissionStates::Unknown) {
                                $downloadPath = constructUrl($tx->getFileName());
                                ?>
                                <a href="<?php p($downloadPath); ?>">Download</a>
                            <?php
                            } else {
                                p('-');
                            } ?>
                        </td>
                        <td>
                            <?php if ($tx->getState() == \OCA\FileRouter\TransmissionStates::PendingReview) { ?>
                                <a href="?action=approve&tx=<?php p($tx->getId()); ?>">Approve</a>&nbsp;<a
                                    href="?action=reject&tx=<?php p($tx->getId()); ?>">Reject</a>
                            <?php
                            } else {
                                p('-');
                            } ?>
                        </td>
                        <td>04:25:00</td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
        <?php if (count($txReceive) > 0) { ?>
            <h1>Transmissions to Download</h1>

            <p>The table below lists <b><?php count($txReview); ?></b> incoming transmissions sent to your organization.
            </p>

            <table class="txTable">
                <tr>
                    <th>Transmission ID</th>
                    <th>Sending Time</th>
                    <th>Sender</th>
                    <th>File Size</th>
                    <th>State</th>
                    <th>Download</th>
                    <th>Available For</th>
                </tr>
                <?php foreach ($txReceive as $tx) { ?>
                    <tr class="<?php if ($alt) {
                        p('alt');
                    }
                    $alt = !$alt; ?>">
                        <td><?php p($tx->getId()); ?></td>
                        <td><?php p($tx->getFileTime()); ?></td>
                        <td><?php p($tx->getSender()); ?></td>
                        <td><?php p($tx->getFileSize()); ?></td>
                        <td><?php p($tx->getStatelabel()); ?></td>
                        <td>
                            <?php if ($tx->getState() == \OCA\FileRouter\TransmissionStates::Released || $tx->getState() == \OCA\FileRouter\TransmissionStates::AutoReleased) {
                                $downloadPath = constructUrl($tx->getFileName());
                                ?>
                                <a href="<?php p($downloadPath); ?>">Download</a>
                            <?php
                            } else {
                                p('-');
                            } ?>
                        </td>
                        <td>01:12:39</td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    <?php } else { ?>
        <h4>No Transmission activity available to view.</h4>
    <?php } ?>
</div>