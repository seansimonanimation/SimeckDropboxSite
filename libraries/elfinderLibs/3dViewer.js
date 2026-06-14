/**
 * 3dViewer.js
 *
 * Opens .obj and .fbx 3D models in a Three.js WebGL viewer.
 * Uses dynamic import() from the submoduled three.js repo at /libraries/threejs/.
 *
 * Exports nothing — attaches a single function to window.open3DViewer.
 */

(function() {

    'use strict';

    const THREE_PATH = '/libraries/threejs/build/three.module.js';
    const LOADERS_PATH = '/libraries/threejs/examples/jsm/loaders/';
    const CONTROLS_PATH = '/libraries/threejs/examples/jsm/controls/OrbitControls.js';

    // ─── Singleton cache for loaded modules ───────────────────────────────
    let _threeModule = null;
    let _controlsModule = null;
    let _OBJLoaderModule = null;
    let _MTLLoaderModule = null;
    let _FBXLoaderModule = null;
    let _loadPromise = null;

    /**
     * Load (or return cached) Three.js + OrbitControls + needed loader.
     * @param {string} ext - file extension (lowercase, no dot)
     * @returns {Promise<{THREE, OrbitControls, loader: Function}>}
     */
    async function loadDependencies(ext) {
    if (_loadPromise) return _loadPromise;

    _loadPromise = (async () => {
        // ─── Inject importmap for 'three' if not already present ────────
        if (!document.querySelector('script[type="importmap"]')) {
            const map = document.createElement('script');
            map.type = 'importmap';
            map.textContent = JSON.stringify({
                imports: {
                    'three': '/libraries/threejs/build/three.module.js'
                }
            });
            document.head.appendChild(map);
            // Give the browser a microtask to process the importmap
            await new Promise(r => setTimeout(r, 0));
        }

        const THREE = await import('/libraries/threejs/build/three.module.js');
        const OrbitControls = (await import('/libraries/threejs/examples/jsm/controls/OrbitControls.js')).OrbitControls;

        let LoaderClass = null;
        let MtlLoaderClass = null;

        if (ext === 'obj') {
            const OBJMod = await import('/libraries/threejs/examples/jsm/loaders/OBJLoader.js');
            LoaderClass = OBJMod.OBJLoader;
            try {
                const MtlMod = await import('/libraries/threejs/examples/jsm/loaders/MTLLoader.js');
                MtlLoaderClass = MtlMod.MTLLoader;
            } catch (e) {
                // No MTLLoader available — proceed without
            }
        } else if (ext === 'fbx') {
            const FBXMod = await import('/libraries/threejs/examples/jsm/loaders/FBXLoader.js');
            LoaderClass = FBXMod.FBXLoader;
        }

        _threeModule = THREE;
        _controlsModule = OrbitControls;
        _OBJLoaderModule = LoaderClass;
        _MTLLoaderModule = MtlLoaderClass;
        _FBXLoaderModule = LoaderClass;

        return { THREE, OrbitControls, LoaderClass, MtlLoaderClass };
    })();

    return _loadPromise;
}


    // ─── Prepare the container with a canvas ─────────────────────────────
    function setupContainer(containerEl) {
        // Clear any previous content
        containerEl.innerHTML = '';

        // Create canvas
        const canvas = document.createElement('canvas');
        canvas.style.display = 'block';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        containerEl.appendChild(canvas);

        return canvas;
    }

    // ─── Build the scene, renderer, controls ─────────────────────────────
    function createScene(THREE, OrbitControls, canvas, containerEl) {
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x1a1a1a);

        // Camera
        const aspect = containerEl.clientWidth / containerEl.clientHeight || 1;
        const camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000);
        camera.position.set(3, 2, 5);
        camera.lookAt(0, 0, 0);

        // Renderer
        const renderer = new THREE.WebGLRenderer({
            canvas: canvas,
            antialias: true,
            alpha: false
        });
        renderer.setSize(containerEl.clientWidth, containerEl.clientHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.2;

        // Controls
        const controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.08;
        controls.autoRotate = true;
        controls.autoRotateSpeed = 1.5;
        controls.minDistance = 0.5;
        controls.maxDistance = 50;
        controls.target.set(0, 0, 0);
        controls.update();

        return { scene, camera, renderer, controls };
    }

    // ─── Lighting ────────────────────────────────────────────────────────
    function addLighting(THREE, scene) {
        // Ambient
        const ambient = new THREE.AmbientLight(0x404060, 0.6);
        scene.add(ambient);

        // Key light
        const key = new THREE.DirectionalLight(0xffffff, 1.8);
        key.position.set(5, 8, 5);
        key.castShadow = true;
        scene.add(key);

        // Fill light
        const fill = new THREE.DirectionalLight(0x8888ff, 0.6);
        fill.position.set(-5, 2, 5);
        scene.add(fill);

        // Back rim light
        const rim = new THREE.DirectionalLight(0xffffff, 0.4);
        rim.position.set(0, 3, -8);
        scene.add(rim);
    }

    // ─── Helpers (grid + axes) ───────────────────────────────────────────
    function addHelpers(THREE, scene) {
        const grid = new THREE.GridHelper(10, 20, 0x888888, 0x444444);
        grid.position.y = -0.01;
        scene.add(grid);

        const axes = new THREE.AxesHelper(2);
        scene.add(axes);
    }

    // ─── Animation loop ──────────────────────────────────────────────────
    function startAnimationLoop(renderer, scene, camera, controls) {
        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }
        animate();
    }

    // ─── Handle resize ───────────────────────────────────────────────────
    function addResizeHandler(containerEl, renderer, camera) {
        function onResize() {
            const w = containerEl.clientWidth;
            const h = containerEl.clientHeight;
            if (w === 0 || h === 0) return;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
        }

        // Use ResizeObserver if available, else fallback to window resize
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(onResize);
            ro.observe(containerEl);
            return () => ro.disconnect();
        } else {
            window.addEventListener('resize', onResize);
            return () => window.removeEventListener('resize', onResize);
        }
    }

    // ─── Show fallback UI for unsupported formats (blend, mb) ────────────
    function showUnsupportedUI(containerEl, fileName, ext, fileUrl) {
        const extUpper = ext.toUpperCase();
        let appName = 'the native application';
        if (ext === 'blend') appName = 'Blender';
        if (ext === 'mb') appName = 'Autodesk Maya';

        containerEl.innerHTML = `
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#bbb;font-family:sans-serif;text-align:center;padding:40px;box-sizing:border-box;">
                <div style="font-size:64px;margin-bottom:16px;">📦</div>
                <h2 style="margin:0 0 8px;color:#fff;">${fileName}</h2>
                <p style="margin:0 0 20px;font-size:0.9rem;color:#999;">
                    .${ext} files cannot be previewed in the browser.
                </p>
                <p style="margin:0 0 24px;font-size:0.85rem;color:#777;">
                    Open this file in ${appName} to view the 3D model.
                </p>
                <a href="${encodeURI(fileUrl)}" download
                   style="display:inline-block;padding:10px 28px;background:#4a7cff;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;">
                    ⬇ Download File
                </a>
            </div>
        `;
    }

    // ─── Main entry point ────────────────────────────────────────────────
    window.open3DViewer = async function(containerEl, fileUrl, extension, fileName) {
        if (!containerEl || !fileUrl || !extension) {
            console.error('open3DViewer: missing required arguments.');
            return;
        }

        const ext = extension.toLowerCase().replace(/^\./, '');

        // Unsupported formats
        if (ext === 'blend' || ext === 'mb') {
            showUnsupportedUI(containerEl, fileName, ext, fileUrl);
            return;
        }

        // Only .obj and .fbx are supported
        if (ext !== 'obj' && ext !== 'fbx') {
            containerEl.innerHTML = `<p style="color:#888;text-align:center;padding:40px;">Unsupported 3D format: .${ext}</p>`;
            return;
        }

        // Show loading indicator
        containerEl.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#888;font-family:sans-serif;">Loading 3D model…</div>';

        try {
            // Dynamically import dependencies
            const { THREE, OrbitControls, LoaderClass, MtlLoaderClass } = await loadDependencies(ext);

            // Setup container
            const canvas = setupContainer(containerEl);

            // Create scene
            const { scene, camera, renderer, controls } = createScene(THREE, OrbitControls, canvas, containerEl);
            addLighting(THREE, scene);
            addHelpers(THREE, scene);

            // Create loader
            const loader = new LoaderClass();

            // Special handling for .obj with optional .mtl
            if (ext === 'obj' && MtlLoaderClass) {
                // Try to guess the .mtl path (same name, .mtl extension)
                const mtlUrl = fileUrl.replace(/\.obj$/i, '.mtl');
                const mtlLoader = new MtlLoaderClass();

                try {
                    const materials = await new Promise((resolve, reject) => {
                        mtlLoader.load(mtlUrl, resolve, undefined, reject);
                    });
                    materials.preload();
                    loader.setMaterials(materials);
                } catch (e) {
                    // No .mtl found — proceed without materials
                    console.warn('No .mtl file found at', mtlUrl, '- proceeding without materials');
                }
            }

            // Load the model
            const object = await new Promise((resolve, reject) => {
                loader.load(fileUrl, resolve, undefined, reject);
            });

            // Center and scale the model
            const box = new THREE.Box3().setFromObject(object);
            const center = box.getCenter(new THREE.Vector3());
            const size = box.getSize(new THREE.Vector3());
            const maxDim = Math.max(size.x, size.y, size.z);
            const scale = maxDim > 0 ? (2.0 / maxDim) : 1;
            object.position.sub(center.clone().multiplyScalar(scale));
            object.scale.setScalar(scale);

            // Enable shadows on all meshes
            object.traverse(child => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add(object);

            // Hide loading indicator
            containerEl.querySelector('canvas').style.display = 'block';

            // Start animation
            startAnimationLoop(renderer, scene, camera, controls);

            // Handle resize
            const cleanupResize = addResizeHandler(containerEl, renderer, camera);

            // Store cleanup on container for when the island is closed
            containerEl._cleanup3D = function() {
                cleanupResize();
                renderer.dispose();
                // Dispose geometries and materials
                scene.traverse(child => {
                    if (child.isMesh) {
                        child.geometry?.dispose();
                        if (child.material) {
                            if (Array.isArray(child.material)) {
                                child.material.forEach(m => m.dispose());
                            } else {
                                child.material.dispose();
                            }
                        }
                    }
                });
            };

        } catch (err) {
            console.error('3D viewer error:', err);
            containerEl.innerHTML = `
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#bbb;font-family:sans-serif;text-align:center;padding:40px;box-sizing:border-box;">
                    <p style="font-size:1.1rem;margin-bottom:12px;">❌ Failed to load 3D model</p>
                    <p style="font-size:0.85rem;color:#999;">${err.message || 'Unknown error'}</p>
                </div>
            `;
        }
    };

})();
