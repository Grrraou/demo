// Custom Jitsi Config - Disable moderation and lobby

// Fix WebSocket URL - use BOSH instead which is more reliable
config.bosh = '/http-bind';
config.websocket = null;

config.prejoinConfig = {
    enabled: false,
    hideDisplayName: false
};

config.requireDisplayName = false;
config.enableLobbyChat = false;
config.hideLobbyButton = true;
config.disableLobby = true;
config.enableInsecureRoomNameWarning = false;
config.enableWelcomePage = false;
config.enableClosePage = false;

// Disable authentication-related features
config.enableUserRolesBasedOnToken = false;
config.enableFeaturesBasedOnToken = false;

// Everyone is a moderator (no special privileges)
config.disableModeratorIndicator = true;

// Start with devices ready
config.startWithAudioMuted = false;
config.startWithVideoMuted = false;

// Disable notifications that might confuse users
config.notifications = [];

// P2P configuration for better performance
config.p2p = {
    enabled: true,
    stunServers: [
        { urls: 'stun:meet-jit-si-turnrelay.jitsi.net:443' }
    ]
};

// Disable analytics
config.analytics = {
    disabled: true
};
