<style>
    .friend {
        border-radius: 60px;
        float: left;
        padding: 10px;
        text-align: center;
        width: 64px;
    }

    .clear {
        content:'';
        display: table;
        clear: both;
    }

    .block-subheader {
        font-size:13px;color:#1c374c;
    }

</style>
{if count($referals)}
    <div class="panel panel-default sidebar flat widget widget-userfeed">
        <div class="panel-body pab24">
            <h4 class="panel-header">
                <i class="fa fa-users"></i>
                {$aLang.plugin.referals.referals}
            </h4>
            <div class="widget-content">
                <ul class="list-unstyled">
                    {foreach $referals as $oUser}

                        <li data-alto-role="popover"
                            data-user-id="{$oUser->getId()}"
                            data-api="user/{$oUser->getId()}/info"
                            data-api-param-tpl="default"
                            data-trigger="hover"
                            data-placement="left"
                            data-animation="true"
                            data-cache="true" class="friend">
                            <a href="{$oUser->getUserWebPath()}">
                                <img src="{$oUser->getProfileAvatarPath(64)}" alt="avatar"/>
                            </a><br/>
                            <a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div class="clear"></div>
            {if $user and ($oUserProfile->getLogin() == $user->getLogin())}
                <div class="block-subheader">
                    {$aLang.plugin.referals.referal_link}:<br> {router page='ref'}{$oUserProfile->getLogin()}/
                </div>
            {/if}
        </div>
    </div>
{/if}
