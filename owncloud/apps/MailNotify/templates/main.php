<!-- Start of MailNotify Main template -->
<script type="text/javascript">
    if (!console) {console = {log: function() {}};}
    var notifications = <?php print_unescaped(json_encode($_['notificationLog'])); ?>;
    function showNotification(notificationId) {
        var len = notifications.length;
        for (var index = 0; index < len; index++) {
            console.log(notificationId, notifications[index].nid);
            if (notificationId === notifications[index]["nid"]) {
                alert(notifications[index]["message"]);
                return;
            }
        }
    }
</script>
<div class="notificationLog">
    <h1>Alert Log</h1>
    <hr/>
    <table class="logTable">
        <tr>
            <th>Trans. ID</th>
            <th>Notice ID</th>
            <th>Type</th>
            <th>Time</th>
            <th>Code</th>
            <th>Source</th>
            <th>Recipients</th>
            <th>Mailed</th>
            <th>View</th>
        </tr>
        <?php $alt = true; ?>
        <?php foreach ($_['notificationLog'] as $notification) { ?>
            <?php if ($alt) { ?>
                <tr class="alt">
            <?php } else { ?>
                <tr>
            <?php } ?>
            <?php $alt = !$alt; ?>
            <td><?php p($notification->getTransmissionid()); ?></td>
            <td><?php p($notification->getNid()); ?></td>
            <td><?php p($notification->getTypeLabel()); ?></td>
            <td><?php p($notification->getTimeString()); ?></td>
            <td><?php p($notification->getNcode()); ?></td>
            <td><?php p($notification->getOrigin()); ?></td>
            <td><?php p($notification->getRecipientString()); ?></td>
            <td><?php p($notification->getSentLabel()); ?></td>
            <td>
                <a href="#"
                   onclick="showNotification('<?php print_unescaped($notification->getNid()); ?>');return false;"><img
                        src="<?php p(\OCP\Util::imagePath('MailNotify', 'mg_icon.png')); ?>"/></a>
            </td>
            </tr>
        <?php } ?>
    </table>
</div>
<!-- End of MailNotify Main template -->
