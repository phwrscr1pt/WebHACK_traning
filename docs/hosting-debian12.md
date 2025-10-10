# Hosting the LOCTH Lab on Debian 12

These instructions walk through deploying the intentionally vulnerable LOCTH Lab on a Debian 12 virtual machine like the one you described (4 vCPU / 8 GB RAM). The lab relies on Docker Compose to run a PHP web server, MySQL database, and optional phpMyAdmin instance.

> ⚠️ **Warning:** The application is deliberately insecure and should only run on an isolated training network. Never expose the lab directly to the internet.

## 1. Prepare the operating system

1. Log in to the Debian 12 VM as a user with sudo privileges.
2. Update the system and install a few prerequisite packages:

   ```bash
   sudo apt update
   sudo apt upgrade -y
   sudo apt install -y ca-certificates curl gnupg lsb-release
   ```

## 2. Install Docker Engine and Compose plugin

Follow Docker's official repository instructions so that you receive the latest stable releases.

```bash
# Add Docker's GPG key and repository
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine and the Compose plugin
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Allow your user to run docker commands (optional but recommended)
sudo usermod -aG docker $USER
```

Log out and back in so the new group membership takes effect if you enabled the optional `usermod` step.

## 3. Obtain the lab source code

Clone the repository (or copy your existing code) into a working directory, e.g. `/opt/locth-lab`:

```bash
sudo mkdir -p /opt/locth-lab
sudo chown $USER:$USER /opt/locth-lab
cd /opt/locth-lab

git clone https://example.com/your/WebHACK_traning.git .
```

If you already copied the files onto the VM, simply ensure they live in a directory you control (`/opt/locth-lab` in this guide).

## 4. Configure environment variables

The application reads database credentials and flag values from a `.env` file in the project root. Start with the provided defaults and customize as needed:

```bash
cp .env.example .env  # create if the example file is present
```

At minimum ensure the following keys match the values defined in `docker-compose.yml`:

```env
DB_HOST=db
DB_NAME=locth_lab
DB_USER=labuser
DB_PASS=labpass
DB_ROOT_PASS=rootpass

FLAG1=LOCTH{header_sequence_ok}
FLAG2=LOCTH{otp_bypass_ready}
FLAG3=LOCTH{union_dump_success}
FLAG4=LOCTH{idor_masterpiece}
FLAG5=LOCTH{shell_upload_complete}
```

You can edit the flag values to whatever tokens you want trainees to capture.

## 5. Launch the stack with Docker Compose

From the project root run:

```bash
docker compose up --build -d
```

The command builds the custom PHP image (installing required extensions) and starts three services:

- `web`: Apache + PHP 8.2 serving the lab on port **8080**
- `db`: MySQL 8.0 seeded with challenge data
- `pma`: phpMyAdmin on port **8081** (optional helper UI)

Verify the containers are healthy:

```bash
docker compose ps
```

Once the services report `healthy`/`running`, browse to `http://<vm-ip>:8080` from your training workstation to access the lab.

## 6. Persistent data and uploads

- MySQL data persists inside the named Docker volume `db_data`.
- User-uploaded files land in `public/uploads/` which is bind-mounted into the container. Ensure the directory exists and is writable:

  ```bash
  mkdir -p public/uploads
  chmod 777 public/uploads
  ```

Because the application is intentionally vulnerable, periodically clear uploads and reset the database using:

```bash
docker compose down -v  # stops services and removes the db_data volume
```

Then re-run `docker compose up -d` to rebuild a clean lab state.

- The `flags/` directory is mounted read-only, so whatever `final_flag.txt` contains on the host will be exposed to trainees.

## 7. (Optional) Start on boot with systemd

Create `/etc/systemd/system/locth-lab.service` with the following content:

```ini
[Unit]
Description=LOCTH Web Security Lab
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/locth-lab
ExecStart=/usr/bin/docker compose up -d
ExecStop=/usr/bin/docker compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

Enable the service so the lab starts automatically after reboots:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now locth-lab.service
```

## 8. Troubleshooting tips

- Run `docker compose logs -f` to view container logs if a service fails.
- Confirm ports 8080/8081 are open in any host-based firewall (Debian disables firewalld by default).
- If Docker complains about resources, ensure the VM still has at least ~2 GB of free disk space and sufficient RAM.

With the stack running you can begin the five-stage challenge covering HTTP header manipulation, SQL injection, IDOR, and malicious file uploads.
