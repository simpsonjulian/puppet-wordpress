<?php

function wpjobads_template_job_listing($args)
{
    $colors = array('fulltime' => '#009900', 'parttime' => '#663366', 'freelance' => '#FE8433', 'internship' => '#000000');
    $labels = array('fulltime' => __('Full Time', 'wpjobads'), 'parttime' => __('Part Time', 'wpjobads'), 'freelance' => __('Freelance', 'wpjobads'), 'internship' => __('Internship', 'wpjobads'));
    extract($args);
?>
    <?php foreach ($jobs as $_job): ?>
        <h3><?php echo attribute_escape($_job['label']) ?></h3>
        <?php if (empty($_job['listing'])): continue; endif; ?>
        <table width="100%" style="border-collapse: collapse;font-family: 'Helvetica';text-align: left;margin: 1em 0;">
        <?php foreach ($_job['listing'] as $i => $job): ?>
            <?php if ($i % 2): ?>
            <tr style="background-color: #FFF;border-bottom: 1px solid #CCC;">
            <?php else: ?>
            <tr style="background-color: #F9F9F9;border-bottom: 1px solid #CCC;">
            <?php endif ?>
                <td valign="top" align="center" width="10%" style="font-size:8px;"><a style="color:white;background-color:<?php echo $colors[$job['type']] ?>;padding:1px;font-weight:bold;text-transform:uppercase;" href="<?php echo wpjobads_get_permalink('jobtype=' . attribute_escape($job['type'])) ?>"><?php echo attribute_escape($labels[$job['type']]) ?></a></td>
                <td valign="top" align="center" width="10%" style="padding: .25em;font-weight: bold;"><?php echo attribute_escape(date($date_format, strtotime($job['posted']) + $gmt_offset)) ?></td>
                <td align="left" width="75%">
                    <?php if ($job['company_url']): ?>
                    <?php echo sprintf(__('%1$s at %2$s', 'wpjobads'), '<a href="' . wpjobads_get_permalink('job_id=' . attribute_escape($job['id'])) . '">' . attribute_escape($job['title']) . '</a>', '<a href="' . attribute_escape($job['company_url']) . '">' . attribute_escape($job['company_name']) . '</a>') ?>
                    <?php else: ?>
                    <?php echo sprintf(__('%1$s at %2$s', 'wpjobads'), '<a href="' . wpjobads_get_permalink('job_id=' . attribute_escape($job['id'])) . '">' . attribute_escape($job['title']) . '</a>', attribute_escape($job['company_name'])) ?>
                    <?php endif ?>
                    <span style="font-size: .9em;color: #666;">(<?php echo attribute_escape($job['location']) ?>)</span>
                </td>
            </tr>
        <?php endforeach ?>
        </table>
    <?php endforeach ?>

    <?php if (empty($jobs) or (isset($cat_ID) and empty($jobs[$cat_ID]['listing']))): ?>
    <p><?php _e('No jobs yet.', 'wpjobads') ?></p>
    <?php endif  ?>

    <?php if ($enable_frontend): ?>
        <?php if (isset($cat_ID)): ?>
        <a href="<?php echo wpjobads_get_permalink('action=postjob&cat_ID=' . attribute_escape($cat_ID)) ?>"><?php echo attribute_escape($invite) ?></a>
        <?php else: ?>
        <a href="<?php echo wpjobads_get_permalink('action=postjob') ?>"><?php echo attribute_escape($invite) ?></a>
        <?php endif ?>
    <?php endif ?>
<?php
}

function wpjobads_template_view_job($args)
{
    $labels = array('fulltime' => __('Full Time', 'wpjobads'), 'parttime' => __('Part Time', 'wpjobads'), 'freelance' => __('Freelance', 'wpjobads'), 'internship' => __('Internship', 'wpjobads'));
    extract($args);
?>
    <?php if ($job['company_url']): ?>
    <a href="<?php echo attribute_escape($job['company_url']) ?>"><?php echo $job['company_name'] ?></a>
    <?php else: ?>
    <?php echo $job['company_name'] ?>
    <?php endif ?>
    <address><p><?php echo $job['location'] . ' ' . $job['zipcode'] ?></p></address>
    <div>
        <?php echo $job['description'] ?>
    </div>
    <h4><?php _e('Interested?', 'wpjobads') ?></h4>
    <?php echo $job['how_to_apply'] ?>
<?php
}

function wpjobads_template_random_ad($args)
{
    extract($args);
?>
    <div class="postmetadata alt" style="text-align:center;">
        <small>
            <a href="<?php echo wpjobads_get_permalink('job_id=' . attribute_escape($job->id)) ?>"><?php echo sprintf(__('%1$s is looking for a %2$s', 'wpjobads'), attribute_escape($job->company_name), attribute_escape($job->title)) ?></a>
            <br/>
            <a href="<?php echo wpjobads_get_permalink() ?>"><?php _e('Looking for a job? Got a position to fill? Check out the Job Board.', 'wpjobads') ?></a>
        </small>
    </div>
<?php
}

?>
