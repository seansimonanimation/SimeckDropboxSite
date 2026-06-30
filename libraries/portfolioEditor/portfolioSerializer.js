/**
 * portfolioSerializer.js — Serialize/deserialize portfolio state to/from JSON
 */

const PortfolioSerializer = {
    /**
     * Deserialize portfolio.json data into the editor state.
     */
    deserialize(jsonData) {
        const pieces = new Map();
        if (jsonData.pieces && Array.isArray(jsonData.pieces)) {
            for (const piece of jsonData.pieces) {
                pieces.set(piece.id, {
                    id: piece.id,
                    type: piece.type || 'image',
                    filename: piece.filename || null,
                    x: piece.x || 0,
                    y: piece.y || 0,
                    z: piece.z || 0,
                    rot: piece.rot || 0,
                    scaleX: piece.scaleX || 1.0,
                    scaleY: piece.scaleY || 1.0,
                    galleryOrder: piece.galleryOrder || 1,
                    caption: piece.caption || '',
                    textContent: piece.textContent || null,
                    fontSize: piece.fontSize || 14,
                    baseWidth: piece.baseWidth || 200,
                    baseHeight: piece.baseHeight || 48
                });
            }
        }

        return {
            pieces,
            profile: {
                username: jsonData.artist?.username || '',
                displayName: jsonData.artist?.display_name || '',
                bio: jsonData.artist?.bio || '',
                links: jsonData.links || []
            },
            publishPortfolio: jsonData.publish_portfolio === 1
        };
    },

    /**
     * Serialize editor state into portfolio.json structure.
     */
    serialize(state) {
        const piecesArray = [];
        for (const piece of state.pieces.values()) {
            piecesArray.push({
                id: piece.id,
                type: piece.type,
                filename: piece.filename,
                x: piece.x,
                y: piece.y,
                z: piece.z,
                rot: piece.rot,
                scaleX: piece.scaleX,
                scaleY: piece.scaleY,
                galleryOrder: piece.galleryOrder,
                caption: piece.caption || '',
                textContent: piece.type === 'text' ? piece.textContent : undefined,
                fontSize: piece.type === 'text' ? (piece.fontSize || 14) : undefined,
                baseWidth: piece.type === 'text' ? (piece.baseWidth || 200) : undefined,
                baseHeight: piece.type === 'text' ? (piece.baseHeight || 48) : undefined
            });
        }

        // Sort pieces by gallery order for cleaner output
        piecesArray.sort((a, b) => a.galleryOrder - b.galleryOrder);

        return {
            version: 1,
            publish_portfolio: state.publishPortfolio ? 1 : 0,
            last_modified: '',  // Set by server
            artist: {
                username: state.profile.username,
                display_name: state.profile.displayName,
                bio: state.profile.bio
            },
            links: state.profile.links,
            pieces: piecesArray
        };
    },

    /**
     * Generate a UUID v4.
     */
    generateId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
};
