// File: js/websocket-client.js

class WebSocketClient {
    constructor(url) {
        this.url = url;
        this.socket = null;
        this.listeners = {};
    }

    connect() {
        this.socket = new WebSocket(this.url);

        this.socket.onopen = () => {
            console.log('WebSocket connection established');
            this.emit('open');
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.emit('message', data);
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.emit('error', error);
        };

        this.socket.onclose = () => {
            console.log('WebSocket connection closed');
            this.emit('close');
        };
    }

    send(data) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(data));
        } else {
            console.error('WebSocket is not connected');
        }
    }

    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => callback(data));
        }
    }
}

// Create and export a single instance
const wsClient = new WebSocketClient('ws://localhost:8080');
export default wsClient;