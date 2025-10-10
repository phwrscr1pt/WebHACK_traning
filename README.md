# LOCTH Lab

LOCTH Lab is an intentionally vulnerable five-stage web application built for hands-on security training. Trainees practice manipulating HTTP headers, bypassing weak authentication, exploiting SQL injection (boolean and UNION based), abusing IDOR, and weaponizing a file upload bug to reach remote code execution.

> **Warning:** This project is for education in a controlled environment only. Never expose the lab to the public internet.

## Quick start (Docker Compose)

The repository ships with a Docker Compose stack that runs Apache + PHP, MySQL 8, and phpMyAdmin. You only need Docker Engine 24+ with the Compose plugin.

```bash
# clone the repo and enter it
git clone https://example.com/your/WebHACK_traning.git
cd WebHACK_traning

# copy the default environment file if you haven't already
cp .env.example .env

# (optional) edit .env to customise credentials/flags
$EDITOR .env

# launch the lab – the first run builds the PHP image
docker compose up --build -d
```

Once the containers report `running` you can browse to:

- `http://localhost:8080` – vulnerable web app
- `http://localhost:8081` – phpMyAdmin helper UI (credentials default to `labuser` / `labpass`)

Bring the stack down with `docker compose down` (add `-v` to reset the seeded database).

### Deploying on a remote server

1. Install Docker Engine 24+ and the Compose plugin on your host (see [`docs/hosting-debian12.md`](docs/hosting-debian12.md) for a Debian example).
2. Clone this repository onto the server and copy `.env.example` to `.env`.
3. Edit `.env` to change database credentials, root password, and flag values before exposing the lab to trainees.
4. Allow inbound TCP 8080/8081 through the firewall or adjust the published ports in `docker-compose.yml`.
5. Start the stack with `docker compose up -d` (use `--build` the first time or after PHP dependency changes).
6. Persist data by keeping the `db_data` Docker volume; remove it with `docker compose down -v` when you want a fresh reset.
7. The `flags/` directory is mounted read-only into the PHP container so your customised final flag is available at runtime.

## Environment variables & flags

Runtime configuration lives in `.env`. The sample file includes sensible defaults:

```env
DB_HOST=db
DB_NAME=locth_lab
DB_USER=labuser
DB_PASS=labpass

FLAG1=LOCTH{header_sequence_ok}
FLAG2=LOCTH{otp_bypass_ready}
FLAG3=LOCTH{union_dump_success}
FLAG4=LOCTH{idor_masterpiece}
FLAG5=LOCTH{shell_upload_complete}
```

Feel free to swap the `FLAG` values for your own tokens before sharing the lab with participants.

## Challenge flow

1. **Stage 1 – HTTP headers:** manipulate UA, Referer, Date, DNT, X-Forwarded-For, and Accept-Language to bypass a request gate and reveal the first flag. 【F:public/gate.php†L17-L120】
2. **Stage 2 – Boolean SQLi + OTP:** exploit the login form to authenticate as the `staff` role, then break a weak OTP check. 【F:public/login.php†L19-L90】【F:public/otp.php†L72-L170】
3. **Stage 3 – UNION SQLi:** pivot from Stage 2 to dump curator credentials and claim the third flag. 【F:public/login.php†L64-L86】
4. **Stage 4 – IDOR:** abuse predictable note IDs to read the head curator's private note and enable QA mode. 【F:db/init.sql†L23-L38】【F:public/note.php†L135-L212】
5. **Stage 5 – File upload:** upload a polyglot image/PHP payload, execute it via `runner.php`, and capture the final flag from the filesystem. 【F:public/upload.php†L16-L161】【F:public/runner.php†L1-L40】

Progress is tracked in the user session and visualized on `flow.php` so trainees can resume where they left off. 【F:public/flow.php†L1-L152】

## Project structure

```
public/           # web root with PHP challenges
public/uploads/   # writable upload directory (ignored in git)
db/init.sql       # seed database with users and notes
flags/            # flag files for the final stage (ignored in git)
docker/           # PHP configuration tweaks
```

## Hosting on Debian 12

If you're running the lab on a Debian 12 VM (e.g. 4 vCPU / 8 GB RAM) follow the detailed deployment checklist in [`docs/hosting-debian12.md`](docs/hosting-debian12.md). It covers installing Docker, configuring `.env`, managing persistent data, and setting up a systemd unit.
