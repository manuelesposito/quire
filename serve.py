#!/usr/bin/env python3
"""Dev server for the living style guide — http.server with caching OFF.

Why this exists: the specimen pages link the built token CSS
(packages/tokens/dist/css/*.css). Chrome's heuristic caching kept serving
stale copies after `pnpm --filter @quire/tokens build`, which once produced
a half-updated page (fresh dark.css + stale variables.css) that was very
confusing to debug. No-store means a plain reload is always the truth.

Run from the repo root:  python3 serve.py   (port 4321, same as before)
"""
import http.server
import socketserver

PORT = 4321


class NoCacheHandler(http.server.SimpleHTTPRequestHandler):
    def end_headers(self):
        self.send_header("Cache-Control", "no-store, must-revalidate")
        self.send_header("Expires", "0")
        super().end_headers()


if __name__ == "__main__":
    socketserver.TCPServer.allow_reuse_address = True
    with socketserver.TCPServer(("", PORT), NoCacheHandler) as httpd:
        print(f"Serving the style guide (no-cache) at http://localhost:{PORT}")
        print("Start here: http://localhost:4321/apps/docs/specimen/home.html")
        httpd.serve_forever()
