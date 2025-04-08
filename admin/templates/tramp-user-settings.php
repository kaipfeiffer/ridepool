<div class="tramp-user-settings" id="tramp-user-settings-section">
    <h2><?php _e('Tramp User Settings'); ?></h2>
    <p><?php _e('Allow Users to ask for ridings or share ridings and setup personal data'); ?></p>
    <p>#<?php echo $is_tramp_user ?>#<?=$tramp_location_id?>#<?=$tramp_user_id?></p>
    <table class="form-table" role="presentation">
        <?php if (! (IS_PROFILE_PAGE && ! $user_can_edit)) : ?>
            <tr class="user-rich-editing-wrap">
                <th scope="row"><?php _e('Tramp User'); ?></th>
                <td>
                    <input type="checkbox" id="is_tramp_user" name="is_tramp_user" <?php checked($is_tramp_user); ?> />
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <?php if ($is_tramp_user) : ?>
        <table class="form-table" role="presentation">
            <?php if (! (IS_PROFILE_PAGE && ! $user_can_edit)) : ?>
                <?php foreach ($user_columns as $col => $value) : ?>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><?php _e($col); ?></th>
                        <td>
                            <input type="text" name="tramp_user[<?= $col ?>]" id="tramp_user_<?= $col ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
        <h3><?php _e('Tramp User Adress'); ?></h3>
        <table class="form-table" role="presentation">
            <?php if (! (IS_PROFILE_PAGE && ! $user_can_edit)) : ?>
                <?php foreach ($location_columns as $col => $value) : ?>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><?php _e($col); ?></th>
                        <td>
                            <input type="text" name="tramp_location[<?= $col ?>]" id="tramp_location_<?= $col ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    <?php endif; ?>
</div>