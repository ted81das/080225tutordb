<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
    .mainContainer {
        width: 85%;
        display: flex;
        justify-content: space-around;
        margin: 0 auto;
        margin-top: 110px;
    }

    .mainCard {
        text-align: center;
    }

    .sideCard {
        display: flex;
        flex-direction: column;
        width: 400px;
    }

    .bf-logo svg {
        margin-bottom: 0;
        width: 80px;
        height: auto;
    }

    .bf-logo p {
        margin: 0 0 5px 0;
        font-size: 20px;
        color: #46596b;
        font-family: 'Roboto';
        font-weight: 600;
    }

    .bf-logo div {
        margin: 0 0 30px 0;
        display: inline-block;
    }

    .bf-logo div a {
        color: #707b83;
        text-decoration: none;
        font-size: 14px;

    }

    .bf-logo div a:focus-visible {
        color: red;
        border: 1px solid #000;
        padding: 3px;
    }

    .bf-logo div a:focus {
        box-shadow: none;
    }

    .bf-logo div a:hover {
        color: #6518b6;
    }

    .bf-logo div span {
        margin: 0 2px;
        color: #707b83;
    }

    .errorMsg {
        width: 40%;
        padding: 40px 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 30px auto 0 auto;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 5%);
    }

    .errorMsg svg {
        color: #6518b6;
        width: 80px;
        height: auto;
    }

    .errorMsg p {
        font-size: 18px;
        font-family: 'Roboto';
        color: #3b4e5d;
        margin-bottom: 0;
    }

    .successMsg svg {
        color: green;
    }

    .formField {
        margin-top: 20px;
    }

    .formField form {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .formField form p {
        font-weight: 600;
        margin: 0 8px 0 0;
        color: #2c3b47;
    }

    .formField form input {
        cursor: pointer;
        background-color: #6518b6;
        border: none;
        border-radius: 100px;
        padding: 8px 16px;
        color: #fff;
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 5%);
        color: #fff;
        transition: 0.3s all ease;
    }

    .formField form input:hover {
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 15%);
    }

    .backBtn {
        text-align: center;
        margin-top: 25px;
    }

    .btn2 {
        display: inline-flex;
        text-decoration: none;
        align-items: center;
        font-size: 14px;
        background-color: #03a9f4;
        color: #fff;
        padding: 5px 15px;
        border-radius: 100px;
        font-weight: 600;
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 5%);
    }

    .btn2 svg {
        width: 20px;
        margin-right: 5px;
    }

    .btn2:hover {
        color: #fff;
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 15%);
    }

    .footerBtn {
        margin-top: 60px;
        text-align: center;
    }

    .footerBtn a {
        font-weight: 400;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 100px;
        margin-right: 5px;
        transition: 0.3s all ease;
    }

    .subscribeBtn {
        border: 0.15em solid #6518b6;
        color: #6518b6;
        background-color: rgb(255 255 255 / 50%)
    }

    .subscribeBtn:hover {
        color: #6518b6;
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 15%);
    }

    .homeBtn {
        background-color: #0f1923;
        color: #fff;
        border: 0.15em solid #0f1923;
    }

    .homeBtn:hover {
        color: #fff;
        box-shadow: 0px 3px 10px 1px rgb(0 0 0 / 15%);
    }

    .supportLink {
        margin-top: 18px;
    }

    .supportLink a {
        display: inline-block;
    }

    .supportLink a:hover svg {
        color: #6518b6;
    }

    .supportLink a:focus-visible {
        border: 1px solid #000;
        /* padding: 5px; */
    }

    .supportLink a:focus {
        box-shadow: none;
    }

    .supportLink a svg {
        color: #92a5b3;
        width: 20px;
        height: auto;
        margin-right: 10px;
        transition: 0.3s all ease;
    }
</style>


<div class="mainContainer">
    <div class="mainCard">
        <div class="bf-logo">
            <svg width="356" height="356" viewBox="0 0 356 356" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 106.8C0 47.816 47.816 0 106.8 0H249.2C308.184 0 356 47.816 356 106.8V249.2C356 308.184 308.184 356 249.2 356H106.8C47.816 356 0 308.184 0 249.2V106.8Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M249.2 16.6133H106.8C56.9913 16.6133 16.6133 56.9913 16.6133 106.8V249.2C16.6133 299.009 56.9913 339.387 106.8 339.387H249.2C299.009 339.387 339.387 299.009 339.387 249.2V106.8C339.387 56.9913 299.009 16.6133 249.2 16.6133ZM106.8 0C47.816 0 0 47.816 0 106.8V249.2C0 308.184 47.816 356 106.8 356H249.2C308.184 356 356 308.184 356 249.2V106.8C356 47.816 308.184 0 249.2 0H106.8Z" fill="url(#paint0_angular_903_14)" fill-opacity="0.44"/>
            <path d="M232.979 146.947C223.402 146.947 215.647 139.192 215.647 129.615C215.647 120.039 223.402 112.284 232.979 112.284C242.555 112.284 250.31 120.039 250.31 129.615C250.31 139.192 242.555 146.947 232.979 146.947Z" fill="#6518B6"/>
            <path d="M222.94 278.624C222.93 249.394 222.93 220.384 222.93 191.201C222.069 191.142 221.383 191.054 220.697 191.054C196.445 191.047 172.193 191.006 147.941 191.071C138.758 191.096 130.58 193.736 124.327 200.934C121.203 204.53 119.068 208.646 118.963 213.38C118.743 223.312 118.581 233.265 118.976 243.187C119.329 252.065 124.878 257.75 132.244 261.938C137.319 264.824 142.879 265.559 148.558 265.591C160.723 265.662 172.889 265.649 185.055 265.599C190.378 265.577 194.426 267.6 196.903 272.429C200.587 279.612 195.321 289.138 187.261 289.24C171.839 289.435 156.376 289.908 140.997 289.051C121.967 287.992 101.949 274.148 96.3068 252.589C95.3939 249.101 95.1296 245.371 95.0707 241.744C94.9171 232.285 94.8468 222.816 95.1065 213.361C95.4686 200.175 101.134 189.395 110.842 180.63C111.358 180.164 111.888 179.716 112.243 179.406C109.253 175.986 106.104 172.835 103.484 169.293C97.6937 161.465 94.8587 152.608 94.9782 142.803C95.0877 133.819 94.8549 124.83 95.0666 115.849C95.4307 100.411 102.252 88.2789 114.673 79.2994C124.347 72.3059 135.207 68.8128 147.196 68.8267C176.139 68.8603 205.082 68.8256 234.025 68.8436C240.705 68.8477 245.42 72.6125 246.464 78.6444C247.69 85.7315 242.756 92.0485 235.572 92.5612C234.543 92.6347 233.505 92.6011 232.472 92.6011C204.483 92.6029 176.493 92.5733 148.504 92.6218C140.875 92.6351 133.683 94.2391 127.633 99.3024C122.037 103.986 118.793 109.832 118.78 117.259C118.765 125.688 118.755 134.116 118.781 142.545C118.812 152.344 123.985 158.855 132.165 163.483C137.174 166.318 142.617 167.13 148.233 167.135C176.7 167.158 205.166 167.142 233.632 167.149C242.094 167.15 246.881 172.312 246.847 180.746C246.734 208.257 246.783 235.769 246.771 263.281C246.768 267.893 246.793 272.505 246.764 277.117C246.72 283.972 242.076 289.027 235.432 289.501C229.233 289.943 224.019 285.49 222.94 278.624Z" fill="#7F02B7"/>
            <path d="M251.672 133.724C249.324 143.427 240.233 149.012 230.748 146.755C221.451 144.542 215.8 135.418 217.92 126.043C219.994 116.872 229.205 110.973 238.309 112.985C247.953 115.115 253.748 123.998 251.672 133.724Z" fill="#7F02B7"/>
            <defs>
            <radialGradient id="paint0_angular_903_14" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(178 178) rotate(90) scale(178)">
            <stop offset="0.21875" stop-color="#8F00FF"/>
            <stop offset="0.502414" stop-color="#EA74B4"/>
            <stop offset="0.72686" stop-color="#FECB47"/>
            <stop offset="0.957143" stop-color="#F36FFF"/>
            </radialGradient>
            </defs>
            </svg>

            <p>Bit Integrations</p>
            <div>
                <a href="https://docs.bit-integrations.bitapps.pro/" tabindex="1" target="_blank">Docs</a>
                <span>•</span>
                <a href="https://tawk.to/chat/60eac4b6d6e7610a49aab375/1faah0r3e" tabindex="2"
                    target="_blank">Support</a>
            </div>
        </div>

        <?php
    if (isset($_POST, $_POST['disconnect'])) {
        include_once BTCBI_PRO_PLUGIN_BASEDIR . 'includes/Core/Update/API.php';
        $activationStatus = BitApps\BTCBI_PRO\Core\Update\API::disconnectLicense();
        if ($activationStatus === true) { ?>
        <div class="errorMsg">
            <svg width="16px" height="16px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M13.617 3.844a2.87 2.87 0 0 0-.451-.868l1.354-1.36L13.904 1l-1.36 1.354a2.877 2.877 0 0 0-.868-.452 3.073 3.073 0 0 0-2.14.075 3.03 3.03 0 0 0-.991.664L7 4.192l4.327 4.328 1.552-1.545c.287-.287.508-.618.663-.992a3.074 3.074 0 0 0 .075-2.14zm-.889 1.804a2.15 2.15 0 0 1-.471.705l-.93.93-3.09-3.09.93-.93a2.15 2.15 0 0 1 .704-.472 2.134 2.134 0 0 1 1.689.007c.264.114.494.271.69.472.2.195.358.426.472.69a2.134 2.134 0 0 1 .007 1.688zm-4.824 4.994l1.484-1.545-.616-.622-1.49 1.551-1.86-1.859 1.491-1.552L6.291 6 4.808 7.545l-.616-.615-1.551 1.545a3 3 0 0 0-.663.998 3.023 3.023 0 0 0-.233 1.169c0 .332.05.656.15.97.105.31.258.597.459.862L1 13.834l.615.615 1.36-1.353c.265.2.552.353.862.458.314.1.638.15.97.15.406 0 .796-.077 1.17-.232.378-.155.71-.376.998-.663l1.545-1.552-.616-.615zm-2.262 2.023a2.16 2.16 0 0 1-.834.164c-.301 0-.586-.057-.855-.17a2.278 2.278 0 0 1-.697-.466 2.28 2.28 0 0 1-.465-.697 2.167 2.167 0 0 1-.17-.854 2.16 2.16 0 0 1 .642-1.545l.93-.93 3.09 3.09-.93.93a2.22 2.22 0 0 1-.711.478z" />
            </svg>
            <p>License Disconnected Successfully</p>
        </div>
        <?php
        } else { ?>
        <div class="errorMsg">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-alert-triangle">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                </path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <p><?php echo $activationStatus; ?></p>
        </div>
        <div class="formField">
            <form action="" method="post">
                <p>Disconnect this site from license? </p>
                <input type="submit" name="disconnect" value="Disconnect">
            </form>
        </div>
        <?php
        }
    } else {
        if (!empty($integrateStatus['expireIn'])) {
            $expireInDays = (strtotime($integrateStatus['expireIn']) - time()) / DAY_IN_SECONDS;
            if ($expireInDays < 25) {
                $notice = $expireInDays > 0
                    ? sprintf(__('Bit Integrations Pro License will expire in %s days', 'bit-integrations-pro'), (int) $expireInDays)
                    : __('Bit Integrations Pro License is expired', 'bit-integrations-pro')
                ?>
        <div class="errorMsg">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-alert-triangle">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                </path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <p><?php echo $notice; ?></p>
        </div>
        <?php
            }
        } ?>
        <div class="formField">
            <form action="" method="post">
                <p>Disconnect this site from license? </p>
                <input type="submit" name="disconnect" value="Disconnect">
            </form>
        </div>
        <?php
    }
?>

        <div class="supportLink">
            <a href="mailto:support@bitapps.pro" tabindex="6" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-mail">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </a>
            <a href="https://www.bitapps.pro/bit-integrations" tabindex="7" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-globe">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path
                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                    </path>
                </svg>
            </a>
            <a href="https://www.facebook.com/groups/bitcommunityusers" tabindex="8" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-facebook">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </a>
            <a href="https://www.youtube.com/channel/UCjUl8UGn-G6zXZ-Wpd7Sc3g/featured" tabindex="9" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-youtube">
                    <path
                        d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z">
                    </path>
                    <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                </svg>
            </a>
        </div>
        <div class="footerBtn">
            <a href="https://subscription.bitapps.pro/wp/login" class="subscribeBtn">Go to Subscription</a>
            <a href="<?= get_admin_url() ?>admin.php?page=bit-integrations#/"
                class="homeBtn">Go Bit Integrations Dashboard</a>
        </div>
    </div>
    <div class="sideCard">
        <?php include_once BTCBI_PRO_PLUGIN_BASEDIR . 'views/license/cashback.php'; ?>
        <?php include_once BTCBI_PRO_PLUGIN_BASEDIR . 'views/license/facebook.php'; ?>
    </div>
</div>